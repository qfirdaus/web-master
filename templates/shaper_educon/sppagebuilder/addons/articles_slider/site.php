<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

use Joomla\CMS\Router\Route;

//no direct accees
defined('_JEXEC') or die('resticted aceess');

class SppagebuilderAddonArticles_slider extends SppagebuilderAddons
{
  public function render()
  {
    $settings = $this->addon->settings;

    $title            = (isset($settings->title)) ? $settings->title : '';
    $heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : 'h3';
    $layout           = (isset($settings->layout) && $settings->layout) ? $settings->layout : 'default';
    $leading_item     = (isset($settings->leading_item) && $settings->leading_item) ? $settings->leading_item : '';
    $catid            = (isset($settings->catid) && $settings->catid) ? $settings->catid : '';
    $see_all          = (isset($settings->see_all) && $settings->see_all) ? $settings->see_all : 0;
    $social_share     = (isset($settings->social_share) && $settings->social_share) ? $settings->social_share : 0;
    $ordering         = (isset($settings->ordering) && $settings->ordering) ? $settings->ordering : '';
    $limit            = (isset($settings->limit) && $settings->limit) ? $settings->limit : '';
    $hide_thumbnail   = (isset($settings->hide_thumbnail) && $settings->hide_thumbnail) ? $settings->hide_thumbnail : '';

    $show_intro           = (isset($settings->show_intro) && $settings->show_intro) ? $settings->show_intro : '';
    $intro_limit          = (isset($settings->intro_limit) && $settings->intro_limit) ? $settings->intro_limit : '';
    $show_author          = (isset($settings->show_author) && $settings->show_author) ? $settings->show_author : '';
    $show_category        = (isset($settings->show_category) && $settings->show_category) ? $settings->show_category : '';
    $show_date            = (isset($settings->show_date) && $settings->show_date) ? $settings->show_date : '';
    $show_readmore        = (isset($settings->show_readmore) && $settings->show_readmore) ? $settings->show_readmore : '';
    $readmore_text        = (isset($settings->readmore_text) && $settings->readmore_text) ? $settings->readmore_text : '';

    $autoplay     = (isset($settings->autoplay)) ? $settings->autoplay : '';
    $class        = (isset($settings->class)) ? ' ' . $settings->class : '';

    require_once JPATH_COMPONENT . '/helpers/articles.php';
    $items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid);

    //Check Auto Play
    $slide_autoplay = ($autoplay) ? 'data-sppb-slide-ride="true"' : '';
    $slide_controllers = 'data-sppb-slidefull-controllers="true"';

    if (count($items)) {
      $output  = '<div class="sppb-addon sppb-addon-articles-slider ' . $class . '">';
      if ($title) {
        $output .= '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>';
      }

      $output .= '<div class="sppb-addon-content">';
      $output .= '<div class="sppb-row">';
      $output .= '<div class="sppb-col-sm-12">';
      $output .= '<div class="articles-slider owl-carousel" ' . $slide_autoplay . ' >';
      foreach ($items as $key => $item) {

        $item->catUrl = Route::_(ContentHelperRoute::getCategoryRoute($item->catslug));

        $output .= '<div class="sppb-addon-article item">';
        if (!$hide_thumbnail) {
          if ($item->post_format == 'gallery') {
            if (count($item->imagegallery->images)) {

              $output .= '<div class="sppb-carousel sppb-slide" data-sppb-ride="sppb-carousel">';
              $output .= '<div class="sppb-carousel-inner">';
              foreach ($item->imagegallery->images as $gallery_item) {
                $output .= '<div class="sppb-item">';
                $output .= '<img src="' . $gallery_item['small'] . '" alt="' . $item->title . '">';
                $output .= '</div>';
              }
              $output  .= '</div>';
              $output  .= '<a class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
              $output  .= '<a class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';
              $output .= '</div>';
            } elseif (isset($item->image_small) && $item->image_small) {
              $output .= '<a href="' . $item->link . '" class="sppb-img-wrapper" itemprop="url"><img class="sppb-img-responsive" src="' . $item->image_small . '" alt="' . $item->title . '" itemprop="thumbnailUrl"></a>';
            }
          } else {
            if (isset($item->image_small) && $item->image_small) {
              $output .= '<a href="' . $item->link . '" class="sppb-img-wrapper" itemprop="url"><img class="sppb-img-responsive" src="' . $item->image_small . '" alt="' . $item->title . '" itemprop="thumbnailUrl"></a>';
            }
          }
        }

        $output .= '<div class="sppb-article-details">';
        if ($show_author || $show_date || $show_category) {
          $output .= '<div class="sppb-article-meta">';

          if ($show_category) {
            $output .= '<span class="sppb-meta-category"><a href="' . $item->catUrl . '" itemprop="genre">' . $item->category . '</a></span>';
          }

          if ($show_date) {
            $output .= '<span class="sppb-meta-date" itemprop="dateCreated">' . Jhtml::_('date', $item->created, 'M d, Y') . '</span>';
          }

          if ($show_author) {
            $output .= '<span class="sppb-meta-author" itemprop="name">' . $item->username . '</span>';
          }
          $output .= '</div>'; //.sppb-article-meta
        }

        $output .= '<p class="article-title"><a href="' . $item->link . '" itemprop="url">' . $item->title . '</a></p>';

        if ($show_intro) {
          $intro_txt = (strlen($item->introtext) > $intro_limit) ? substr($item->introtext, 0, $intro_limit) : $item->introtext;
          $output .= '<div class="sppb-article-introtext">' . $intro_txt . '</div>';
        }

        if ($show_readmore) {
          $output .= '<a class="sppb-readmore" href="' . $item->link . '" itemprop="url">' . $readmore_text . '</a>';
        }

        $output .= '</div>'; //.sppb-article-details
        $output .= '</div>'; //sppb-addon-article item
      }
      $output .= '</div>'; //sppb-col-sm-12
      $output .= '</div>'; //sppb-row
      $output .= '</div>'; //sppb-addon-content

      $output .= '</div>';
      $output .= '</div>';

      return $output;
    }

    return false;
  }

  public function scripts()
  {
    $app = JFactory::getApplication();
    $base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/js/';
    return array($base_path . 'owl.carousel.min.js');
  }

  public function stylesheets()
  {
    $app = JFactory::getApplication();
    $base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/css/';
    return array($base_path . 'owl.carousel.css', $base_path . 'owl.theme.css');
  }

  public function css()
  {
    $addon_id = '#sppb-addon-' . $this->addon->id;
    $settings = $this->addon->settings;
    $title = (isset($settings->title)) ? $settings->title : '';
    $css = '';

    $title_style = (isset($settings->title_margin_top) && $settings->title_margin_top) ? 'margin-top:' . (int) $settings->title_margin_top . 'px;' : '';
    $title_style .= (isset($settings->title_margin_bottom) && $settings->title_margin_bottom) ? 'margin-bottom:' . (int) $settings->title_margin_bottom . 'px;' : '';
    $title_style .= (isset($settings->title_text_color) && $settings->title_text_color) ? 'color:' . $settings->title_text_color  . ';' : '';
    $title_style .= (isset($settings->title_fontsize) && $settings->title_fontsize) ? 'font-size:' . $settings->title_fontsize . 'px;line-height:' . $settings->title_fontsize . 'px;' : '';
    $title_style .= (isset($settings->title_fontweight) && $settings->title_fontweight) ? 'font-weight:' . $settings->title_fontweight . ';' : '';

    if ($title_style && $title) {
      $css .= $addon_id . ' .sppb-addon-title {';
      $css .= $title_style;
      $css .= '}';
    }

    return $css;
  }

  public function js()
  {
    $addon_id = '#sppb-addon-' . $this->addon->id;
    return 'jQuery( document ).ready(function( $ ) {
        var $slideFullwidth = $("' . $addon_id . ' .articles-slider");

        var $autoplay   = $slideFullwidth.attr("data-sppb-slide-ride");
        if ($autoplay == "true") { var $autoplay = true; } else { var $autoplay = false};

        $slideFullwidth.owlCarousel({
            loop: true,
            video:true,
            nav:true,
            dots: false,
            autoplay: $autoplay,
            autoplayHoverPause: true,
            items: 1,
        });
      });';
  }
}
