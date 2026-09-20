<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

namespace JoomShaper\SPPageBuilder\DynamicContent\Services;

use Joomla\CMS\Factory;
use JoomShaper\SPPageBuilder\DynamicContent\Constants\Conditions;
use JoomShaper\SPPageBuilder\DynamicContent\Constants\FieldTypes;
use JoomShaper\SPPageBuilder\DynamicContent\Model;
use JoomShaper\SPPageBuilder\DynamicContent\Models\CollectionField;
use JoomShaper\SPPageBuilder\DynamicContent\Models\CollectionItem;
use JoomShaper\SPPageBuilder\DynamicContent\Models\CollectionItemValue;
use JoomShaper\SPPageBuilder\DynamicContent\Site\CollectionHelper;
use JoomShaper\SPPageBuilder\DynamicContent\Supports\Arr;
use JoomShaper\SPPageBuilder\DynamicContent\Supports\Str;
use Throwable;

/**
 * Collection Data Service
 * This class is responsible for fetching and preparing collection items for displaying in the frontend.
 *
 * @since 5.5.0
 */
class CollectionDataService
{
    /**
     * Cache for option field data by field ID.
     * 
     * @var array
     * @since 6.2.0
     */
    protected $optionFieldCache = [];
    /**
     * Fetch collection items for the given collection id.
     *
     * @param int $collectionId
     * @return array
     * @throws Throwable
     *
     * @since 5.5.0
     */
    public function fetchCollectionItems(int $collectionId, string $direction = 'ASC', ?int $sortingColumn = null, ?string $language = null)
    {
        try {
            $items = $this->getCollectionItemsByCollectionId($collectionId, $direction, $sortingColumn, $language);
            $items = Arr::make($items);
            return $this->prepareCollectionItems($items);
        } catch (Throwable $error) {
            throw $error;
        }
    }

    /**
     * Fetch collection item by its id.
     *
     * @param int $itemId
     * @return array|null
     *
     * @since 5.5.0
     */
    public function fetchCollectionItemById(int $itemId)
    {
        $item = CollectionItem::where('id', $itemId)
            ->with([
                'values' => function ($query) {
                    $query = $query->select([
                        'field_name' => 'collection_field.name',
                        'field_type' => 'collection_field.type'
                    ]);

                    return $query->leftJoin(
                        CollectionField::class,
                        'collection_item_value.field_id',
                        'collection_field.id'
                    );
                }
            ])->first();

        if ($item->isEmpty()) {
            return null;
        }

        $this->preloadOptionFieldData([$item]);

        return $this->prepareCollectionIndividualItem($item);
    }

    /**
     * Fetch collection items by their IDs.
     *
     * @param array $itemIds The IDs of the items to fetch.
     * @param string $direction The direction of the order.
     * @param int|null $sortingColumn The collection field id to sort the items by.
     * @return array The fetched items.
     *
     * @since 5.5.0
     */
    public function fetchCollectionItemsByItemIds(array $itemIds, string $direction = 'ASC', ?int $sortingColumn = null)
    {
        $query = CollectionItem::whereIn('id', $itemIds)
            ->where('published', 1);

        $query = $this->applySorting($query, $direction, $sortingColumn);

        $items = $query->with([
                'values' => function ($query) {
                    $query = $query->select([
                        'field_name' => 'collection_field.name',
                        'field_type' => 'collection_field.type'
                    ]);

                    return $query->leftJoin(
                        CollectionField::class,
                        'collection_item_value.field_id',
                        'collection_field.id'
                    );
                }
            ])->get();

        if (empty($items)) {
            return [];
        }

        $items = Arr::make($items);

        return $this->prepareCollectionItems($items);
    }

    /**
     * Get collection reference items on demand.
     *
     * @param array $item The item to get reference items for.
     * @param object $filters The filters to apply.
     * @param string $direction The direction of the order.
     * @param int|null $sortingColumn The collection field id to sort the items by.
     * @return array The fetched items.
     *
     * @since 5.5.0
     */
    public function getCollectionReferenceItemsOnDemand($item, $filters, string $direction = 'ASC', ?int $sortingColumn = null)
    {
        if (empty($item) || empty($filters) || empty($filters->conditions)) {
            return [];
        }

        $parentItemId = $item['id'];
        $conditions = $filters->conditions;
        $conditions = Arr::make($conditions);
        $itemIds = $conditions->map(function ( $condition) use ($parentItemId) {
            $fieldId = $condition->variable;
            return $this->getReferenceItemIds($parentItemId, $fieldId);
        })->toArray();

        $itemIds = $filters->match === Conditions::MATCH_ALL
            ? array_intersect(...$itemIds)
            : array_unique(array_merge(...$itemIds));

        if (empty($itemIds)) {
            return [];
        }

        return $this->fetchCollectionItemsByItemIds($itemIds, $direction, $sortingColumn);
    }

    /**
     * Get the reference item IDs.
     *
     * @param int $itemId The item ID.
     * @param int $fieldId The field ID.
     * @return array The reference item IDs.
     *
     * @since 5.5.0
     */
    protected function getReferenceItemIds($itemId, $fieldId)
    {
        $itemIds = CollectionItemValue::where('field_id', $fieldId)
            ->where('item_id', $itemId)
            ->get(['reference_item_id']);

        if (empty($itemIds)) {
            return [];
        }

        return Arr::make($itemIds)->pluck('reference_item_id')->toArray();
    }

    /**
     * Prepare item values by handling reference values.
     * This method visits each value and checks if it has a reference item id.
     * If it does, it sets the is_reference flag to true and assigns the reference item id to the value.
     *
     * @param Arr $values
     * @return Arr
     *
     * @since 5.5.0
     */
    protected function checkReferenceValues(Arr $values)
    {
        return $values->map(function ($value) {
            $value['is_reference']          = $value['field_type'] === FieldTypes::REFERENCE;
            $value['is_multi_reference']    = $value['field_type'] === FieldTypes::MULTI_REFERENCE;

            if (!empty($value['reference_item_id'])) {
                $value['value'] = [$value['reference_item_id']];
            }

            return $value;
        });
    }

    /**
     * Structure collection item values.
     * This method visits each value and checks if it has a reference item id.
     * If it does, it sets the is_reference flag to true and assigns the reference item id to the value.
     *
     * @param Arr $values
     * @return Arr
     *
     * @since 5.5.0
     */
    protected function structureCollectionItem(Arr $values)
    {
        return $values->reduce(function ($carry, $value) {
            $key = CollectionItemsService::FIELD_KEY_PREFIX . $value['field_id'];

            if (!in_array($value['field_type'], [FieldTypes::REFERENCE, FieldTypes::MULTI_REFERENCE])) {
                $carry[$key] = $value['value'];
            }

            return $carry;
        }, []);
    }

    /**
     * Prepare collection items for displaying in the frontend.
     *
     * @param Arr $items
     * @return array
     *
     * @since 5.5.0
     */
    protected function prepareCollectionItems(Arr $items)
    {
        $this->preloadOptionFieldData($items->toArray());
        
        return $items->map(function ($item) {
            $item = $this->prepareCollectionIndividualItem($item);
            $item['url'] = CollectionHelper::createRouteUrl($item);
            return $item;
        })->toArray();
    }

    /**
     * Get the collection option fields data.
     *
     * @param int $fieldId The field ID.
     * @return array The collection option fields data.
     *
     * @since 5.5.0
     */
    protected function getCollectionOptionFieldsData(int $fieldId)
    {
        if (isset($this->optionFieldCache[$fieldId])) {
            return $this->optionFieldCache[$fieldId];
        }

        $field = CollectionField::where('id', $fieldId)->where('type', FieldTypes::OPTION)->first(['options']);
        if ($field->isEmpty()) {
            $this->optionFieldCache[$fieldId] = [];
            return [];
        }

        if (empty($field->options)) {
            $this->optionFieldCache[$fieldId] = [];
            return [];
        }

        $options = Str::toArray($field->options);

        if (empty($options)) {
            $this->optionFieldCache[$fieldId] = [];
            return [];
        }

        $optionData = Arr::make($options)->reduce(function ($carry, $option) {
            $carry[$option['value']] = $option['label'];
            return $carry;
        }, [])->toArray();

        $this->optionFieldCache[$fieldId] = $optionData;

        return $optionData;
    }

    /**
     * Pre-load option field data for all option fields to optimize batch processing.
     * This method analyzes all items and pre-fetches option data for all option fields.
     * 
     * @param array $items The collection items.
     * 
     * @return void
     * @since 6.2.0
     */
    protected function preloadOptionFieldData(array $items)
    {
        $optionFieldIds = [];
        
        foreach ($items as $item) {
            if (!empty($item->values)) {
                foreach ($item->values as $value) {
                    if ($value->field_type === FieldTypes::OPTION) {
                        $optionFieldIds[] = $value->field_id;
                    }
                }
            }
        }
        
        $optionFieldIds = array_unique($optionFieldIds);
        
        if (!empty($optionFieldIds)) {
            $optionFields = CollectionField::whereIn('id', $optionFieldIds)
                ->where('type', FieldTypes::OPTION)
                ->get(['id', 'options']);
            
            foreach ($optionFields as $field) {
                if (!empty($field->options)) {
                    $options = Str::toArray($field->options);
                    
                    if (!empty($options)) {
                        $optionData = Arr::make($options)->reduce(function ($carry, $option) {
                            $carry[$option['value']] = $option['label'];
                            return $carry;
                        }, [])->toArray();
                        
                        $this->optionFieldCache[$field->id] = $optionData;
                    } else {
                        $this->optionFieldCache[$field->id] = [];
                    }
                } else {
                    $this->optionFieldCache[$field->id] = [];
                }
            }
        }
    }

    /**
     * Clear the option field cache.
     * 
     * @return void
     * @since 6.2.0
     */
    public function clearOptionFieldCache()
    {
        $this->optionFieldCache = [];
    }

    /**
     * Prepare a single collection item for displaying in the frontend.
     *
     * @param mixed $item
     * @return array
     *
     * @since 5.5.0
     */
    protected function prepareCollectionIndividualItem($item)
    {
        if ($item instanceof Model) {
            $item = $item->toArray();
        }

        $values = Arr::make($item['values']);
        $item['option_store'] ??= [];
        $item['reference_store'] ??= [];
        $item['multi_reference_store'] ??= [];

        foreach ($values as $value) {
            if ($value['field_type'] === FieldTypes::OPTION) {
                $item['option_store'] = array_merge($item['option_store'], $this->getCollectionOptionFieldsData($value['field_id']));
            } elseif($value['field_type'] === FieldTypes::REFERENCE){
                $item['reference_store'] = array_merge($item['reference_store'], ['field_' . $value['field_id'] => $value['reference_item_id']]);
            } elseif($value['field_type'] === FieldTypes::MULTI_REFERENCE){
                $fieldKey = 'field_' . $value['field_id'];
                $item['multi_reference_store'][$fieldKey] ??= [];
                $item['multi_reference_store'][$fieldKey][] = $value['reference_item_id'];
            }
        }

        $values = $this->structureCollectionItem($this->checkReferenceValues($values))->toArray();
        unset($item['values']);

        return array_merge($item, $values);
    }

    /**
     * Apply the ordering to a collection item query.
     *
     * When a sorting column (a collection field id) is given the items are ordered by that
     * field's stored value, otherwise the collection's own ordering column is used.
     *
     * @param QueryBuilder $query The query to order.
     * @param string $direction The direction of the order.
     * @param int|null $sortingColumn The collection field id to sort the items by.
     * @return QueryBuilder The ordered query.
     *
     * @since 6.8.0
     */
    protected function applySorting($query, string $direction = 'ASC', ?int $sortingColumn = null)
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        if (empty($sortingColumn)) {
            return $query->orderBy('ordering', $direction);
        }

        $sortingColumnField = CollectionField::where('id', $sortingColumn)->first(['type']);

        if ($sortingColumnField->isEmpty()) {
            return $query->orderBy('ordering', $direction);
        }

        $sortingColumnType = $sortingColumnField->type;

        // The field_id condition must live in the join's ON clause (not a WHERE clause),
        // otherwise items with no value row for this field would still be excluded after
        // a LEFT JOIN, since NULL never equals $sortingColumn.
        $query = $query->rawQuery(function ($query) use ($sortingColumn) {
            $query->getQuery()->join(
                'LEFT',
                $query->quoteName('#__sppagebuilder_collection_item_values', 'collection_item_value') . ' ON (' .
                $query->quoteNameWithPrefix('collection_item_value.item_id') . ' = ' . $query->quoteNameWithPrefix('collection_item.id') .
                ' AND ' . $query->quoteNameWithPrefix('collection_item_value.field_id') . ' = ' . (int) $sortingColumn . ')'
            );
            return $query;
        });

        // A secondary sort on 'ordering' keeps ties (including NULLs from the LEFT JOIN)
        // deterministic across requests instead of falling back to arbitrary storage order.
        if ($sortingColumnType === FieldTypes::NUMBER) {
            return $query->rawQuery(function ($query) use ($direction) {
                $query->getQuery()->order('CAST(collection_item_value.value AS DECIMAL(20,10)) ' . $direction);
                return $query;
            })->orderBy('ordering', $direction);
        }

        return $query->orderBy('collection_item_value.value', $direction)->orderBy('ordering', $direction);
    }

    /**
     * Get collection items by collection id.
     *
     * @param int $collectionId
     * @return array
     *
     * @since 5.5.0
     */
    protected function getCollectionItemsByCollectionId(int $collectionId, string $direction = 'ASC', ?int $sortingColumn = null, ?string $language = null)
    {
        $user = Factory::getUser();
        $userAccessLevels = $user->getAuthorisedViewLevels();
        // Callers editing a specific language variant of a page (e.g. the page builder editor)
        // can pass $language explicitly to filter by that language instead of the site's
        // currently active one.
        $langTag = !empty($language) ? $language : Factory::getLanguage()->getTag();

        $query = CollectionItem::where('collection_id', $collectionId)
            ->where('published', 1)
            ->whereIn('language', [$langTag, '*'])
            ->whereIn('access', $userAccessLevels);

        $query = $this->applySorting($query, $direction, $sortingColumn);

        $items = $query->with([
            'values' => function ($query) {
                $query = $query->select([
                    'field_name' => 'collection_field.name',
                    'field_type' => 'collection_field.type'
                ]);

                return $query->leftJoin(
                    CollectionField::class,
                    'collection_item_value.field_id',
                    'collection_field.id'
                );
            }
        ])->get();

        return $items;
    }

    public static function getItemTitleById(int $itemId, int $collectionId)
    {
        $titleFieldId = CollectionField::where('collection_id', $collectionId)
            ->where('type', FieldTypes::TITLE)
            ->first(['id']);

        if($titleFieldId){
            $itemTitle = CollectionItemValue::where('item_id', $itemId)
                ->where('field_id', $titleFieldId->id)
                ->first(['value']);

            if($itemTitle){
                return $itemTitle->value;
            }
        }

        return '';
    }
}
