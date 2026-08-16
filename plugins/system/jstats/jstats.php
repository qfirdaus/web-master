<?php
/**
 * Plugin for Joomla 4 & 5 by https://jstats.de/
 * from Alexander Mueller - SEO NW
 *
 * The GNU General Public License is a free, copyleft license for
 * software and other kinds of works.
 * @license https://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * A plugin that allows you to add custom Head Code
 * Last update: 09.04.2025
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgSystemJstats extends CMSPlugin
{
    protected $app;

    public function onBeforeCompileHead()
    {
        // Nur im Frontend ausführen
        if ($this->app->isClient('site')) {
            $doc  = Factory::getDocument();
            $menu = $this->app->getMenu();

            if ($menu) {
                $activeMenu = $menu->getActive();

                if ($activeMenu) {
                    // Plugin-Parameter: ausgeschlossene Menü-IDs
                    $excludedMenuItems = (array) $this->params->get('excluded_menu_items', []);

                    // Nur Code einfügen, wenn die aktuelle Menü-ID nicht ausgeschlossen ist
                    if (!in_array((string) $activeMenu->id, $excludedMenuItems)) {

                        $customCode = trim((string) $this->params->get('custom_code', ''));

                        // Falls Code vorhanden:
                        if (!empty($customCode)) {
                            // Bei Bedarf Cookies deaktivieren
                            if (strpos($customCode, '/* tracker methods like "setCustomDimension" should be called before "trackPageView" */') !== false
                                && $this->params->get('disable_cookies', '0') === '1') 
                            {
                                $customCode = str_replace(
                                    '/* tracker methods like "setCustomDimension" should be called before "trackPageView" */',
                                    "_paq.push(['disableCookies']);\n/* tracker methods like \"setCustomDimension\" should be called before \"trackPageView\" */",
                                    $customCode
                                );
                            }

                            // (Optional) Einfache Plausibilitätsprüfung oder Eskapierung
                            // ACHTUNG: Kann Tracking-Skripte kaputtmachen; ggf. entfernen.
                            // $customCode = $this->filterOrCheckCustomCode($customCode);

                            // Code in den Head-Bereich einfügen
                            $doc->addCustomTag($customCode);
                        }
                    }
                }
            }
        }
    }

    /**
     * (Beispiel-Funktion) Sehr rudimentäre Plausibilitätsprüfung.
     * Nur als Illustration – in vielen Fällen nicht ratsam, da es JS-Code beschädigen könnte.
     */
    private function filterOrCheckCustomCode($code)
    {
        // Beispiel: Entferne gefährliche Tags wie <script> ... </script> 
        // oder andere potentielle XSS-Vektoren. 
        // Hier nur grob als Demonstration, was man tun KÖNNTE:
        $code = preg_replace('#<script[^>]*?>.*?</script>#is', '', $code);
        
        // Beliebiger weiterer Filter oder Check:
        // - z.B. nur erlaubte Tags: <meta>, <link>, <script src="...">
        // - etc.
        
        return $code;
    }
}
