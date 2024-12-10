<?php
/**
 * @version             4.0.5
 * @package             TAGZ
 * @copyright           Copyright (C) 2014 roosterz.nl. All rights reserved.
 * @license             GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemTagz extends JPlugin
{
    /**
     *  The loaded indicator of helper
     *
     *  @var  boolean
     */
    protected $init;

    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  Joomla Database Object
     *
     *  @var  object
     */
    protected $db;

    /**
     *  Supported Components
     *
     *  @var  array
     */
    protected $supported_components = array("com_content");
    /**
     *  Supported Tagz
     *
     *  @var  array
     */
    protected $supported_tagz = array("og:title", "og:description", "og:image", "twitter:title", "twitter:description", "twitter:image");

    /**
     *  In Admin Mode or Not (used for Fast Edit)
     *
     *  @var  boolean
     */
    protected $in_admin_mode = false;

    /**
     *  Is the current Url taggable (is there a component ID and no category view)
     *
     *  @var  boolean
     */
    protected $taggable_url = false;

    /**
     *  Static Tagz like sitename
     *
     *  @var  array
     */
    protected $static_tagz = array("fb:app_id", "og:site_name", "og:type", "twitter:site", "twitter:card");

    /**
     *  Component Type
     *
     *  @var  string
     */
    protected $component_type;

    /**
     *  Component Id
     *
     *  @var  int
     */
    protected $component_id;

    /**
     *  Active Menu Id
     *
     *  @var  int
     */
    protected $active_menu_id;

    /**
     *  Tag Params
     *
     *  @var  array
     */
    protected $tag_params = array();

    /**
     *  The Tagz
     *
     *  @var  array
     */
    protected $tagz = array();

    /**
     *  Class constructor
     *  Overriding in order to make the component's parameters available through all plugin events
     *
     *  @param  string  &$subject
     *  @param  array   $config
     */
    public function __construct(&$subject, $config = array())
    {
        if (!$this->loadClasses()) {
            return;
        }

        
        $config['params'] = TagzHelper::getParams();
        parent::__construct($subject, $config);
    }

    /**
     *  onBeforeRender event is the default event to add the TAGZ to the document
     *
     *  @return void
     */
    public function onBeforeRender()
    {
        $this->component_type = $this->app->input->get('option');

        $joomla_version = substr(JVERSION, 0, 1);

        if ($joomla_version == 4) { // Add JQuery Framework in Joomla 4
            JHtml::_('jquery.framework');
        }

        if ($this->app->isClient('administrator')) {
            return;
        }
        $this->init();

        // if ($this->app->isClient('administrator') && in_array($this->component_type, $this->supported_components)) {
            // TagzHelper::addBackendFastEditCSS();
            //
            // // For the backend add a link to add the tags from the component in stead of going to TAGZ
            // $component_id = $this->getComponentId($this->component_type, false);
            //
            // // print_r($this->item);
            //
            // if (!is_null($component_id)) {
            // 	$this->addFastEditTAGZ($this->component_type, $component_id);
            // }
        // }
    }

    /**
     *  Adds TAGZ to the document
     *
     *  @return void
     */
    private function init()
    {
        // Load Helper if not in admin
        if (!$this->getHelper() || $this->app->isClient('administrator')) {
            return;
        }

        $this->active_menu_id = $this->app->getMenu()->getActive()->id;
        if ($this->active_menu_id !== false) {
            $this->tag_params_menu = $this->getTagParams('menu', $this->active_menu_id);
        } else {
            $this->tag_params_menu = false;
        }

        // Is the active component not supported and does the active menu item not contain a TAG? Then stop.
        if (!in_array($this->component_type, $this->supported_components) && !$this->tag_params_menu) {
            return;
        }

        $this->component_id = $this->getComponentId($this->component_type);

        if (is_int($this->component_id)) {
            $this->tag_params = $this->getTagParams($this->component_type, $this->component_id);
            $this->taggable_url = true;
        } elseif (!is_int($this->component_id) && !$this->tag_params_menu) {
            return; // No valid component ID found and no active Menu Item Tag: return;
        }

        $automatic_tags_fetching = $this->params->get('automatic_tags_fetching');

        // Are there no parameters defined for the component type and component id and is the url taggable? Try to automatically fetch the TAGZ
        if (!$this->tag_params && $automatic_tags_fetching && $this->taggable_url) {

            // strip the com_ from the component type
            if (($pos = strpos($this->component_type, "_")) !== false) {
                $component_type = substr($this->component_type, $pos+1);
                $results = TagzHelper::getTagInfo($this->component_id, $component_type);
                $this->tag_params = TagzHelper::getParamsfromResults($results);

                if (isset($this->tag_params["fb_title"])) {
                    TagzHelper::storeTagInfo($this->component_id, $component_type, $this->tag_params, $id = -1);
                }
            }
            return;
        }

        $conflict_priority = $this->params->get('conflict_priority');

        if ($this->tag_params && $this->tag_params_menu) { // Conflict since both are filled out.
            if ($conflict_priority == 'menu') { // Choose the menu one, else use the component one
                $this->tag_params = $this->tag_params_menu;
            }
        } elseif (!$this->tag_params && $this->tag_params_menu) { // only menu TAG is enabled
            $this->tag_params = $this->tag_params_menu;
        }

        // Set default image if no image has been found and a default one has been set in the configuration of TAGZ
        if (((!isset($this->tag_params['fb_image'])) || ($this->tag_params['fb_image'] == '')) && ($this->params->get('default_fb_image') != '')) {
            $this->tag_params['fb_image'] = $this->params->get('default_fb_image');
        }
        if (((!isset($this->tag_params['twitter_image'])) || ($this->tag_params['twitter_image'] == '')) && ($this->params->get('default_twitter_image') != '')) {
            $this->tag_params['twitter_image'] = $this->params->get('default_twitter_image');
        }

        if (!$this->tagz = $this->generateTagz($this->tag_params)) {
            return;
        }

        // Custom Url for this page?
        isset($this->tag_params['custom_og_url']) ? $customUrlTag = $this->tag_params['custom_og_url'] : $customUrlTag = '';

        // Get TAGz for each available type
        $this->tagz = array_merge(
            $this->getStaticTagz(),
            $this->getUrlTag($customUrlTag),
            $this->tagz
        );
    }

    /**
     *  This event is triggered after the framework has rendered the application.
     *
     *  @return void
     */
    public function onAfterRender()
    {
        // Load Helper
        if (!$this->getHelper() || ($this->app->isClient('administrator'))) {
            return;
        }

        // Try to remove tags generated by other 3rd party extensions (if this is enabled)
        $this->removeOtherTagz();

        // Sort the TAGZ first for better appearance in code
        sort($this->tagz);

        // Insert the TAGZ in the head of the HTML
        $this->insertTagz($this->tagz);
    }

    /************************
     * onInstallerBeforePackageDownload: needed for the securely updating the extension
     *************************/
    public function onInstallerBeforePackageDownload(&$url, &$headers)
    {
        $uri = JUri::getInstance($url);

        // I don't care about download URLs not coming from our site
        $host = $uri->getHost();
        if ($host != 'www.roosterz.nl') {
            return true;
        }

        // Get the download ID
        $dlid = trim($this->params->get('dlid', ''));

        // If the download ID is invalid, return without any further action
        if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid)) {
            return true;
        }

        // Append the Download ID to the download URL
        if (!empty($dlid)) {
            $uri->setVar('dlid', $dlid);
            $url = $uri->toString();
        }

        return true;
    }

    /**
    *  Add a Fast Edit link in the supported component based on the current Component Type / ID
    *
    */
    private function addFastEditTAGZ($component_type, $component_id)
    {
        $component_type_short = $component_type;

        if (($pos = strpos($component_type, "_")) !== false) {
            $component_type_short = substr($component_type, $pos+1);
        }

        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__tagz'))
            ->where($db->quoteName('component_type') . ' = ' . $db->quote($component_type_short))
            ->where($db->quoteName('component_id') . ' = ' . $db->quote($component_id));
        $db->setQuery($query);

        $result = $db->loadResult();
        is_null($result) ? $id = "" : $id = $result;

        $options = [
                'title'       => 'Edit TAG',
                'url'         => JRoute::_('index.php?option=com_tagz&view=tag&layout=edit_modal&id='.$id.'&component_type='.$component_type_short.'&component_id='.$component_id),
                'height'      => '400px',
                'width'       => '800px',
                'backdrop'    => 'static',
                'bodyHeight'  => '70',
                'modalWidth'  => '70',
                'footer'      => '<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
                        . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>
                        <button type="button" class="btn btn-primary" aria-hidden="true"
                         onclick="jQuery(\'#floating_tagz iframe\').contents().find(\'#saveBtn\').click();">'
                        . JText::_('JSAVE') . '</button>
                        <button type="button" class="btn btn-success" aria-hidden="true"
                        onclick="jQuery(\'#floating_tagz iframe\').contents().find(\'#applyBtn\').click();">'
                        . JText::_('JAPPLY') . '</button>',
            ];

        $new_html = '<div id="floating_tagz"><a href="#tagz_modal" data-toggle="modal" class="floating_tagz_icon" data-tooltip="Add / Edit TAGZ"></a></div>';
        echo $new_html;

        echo JHtml::_('bootstrap.renderModal', 'tagz_modal', $options);
    }

    /**
     *  Get the Component Id based on the Component Type
     *
     *  @return  integer
     */
    private function generateTagz($tag_params)
    {
        if (!isset($tag_params)) {
            return false;
        }

        $html = array();

        foreach ($this->supported_tagz as $key => $supported_tag) {
            if (strpos($supported_tag, "og:") !== false) {
                $tag = str_replace("og:", "fb_", $supported_tag);

                if ((isset($tag_params[$tag])) && ($tag_params[$tag] != "")) { // TAG info found
                    if (strpos($tag, "image") !== false) { // Image tag, prepend base Url?
                        $baseUrl = JURI::root();
                        if ((strpos($tag_params[$tag], $baseUrl) === false) && (strpos($tag_params[$tag], 'http') === false)) { // Only add the base Url if it hasn't been added before or is an external image
                            $tag_params[$tag] = $baseUrl . $tag_params[$tag];
                        }

                        // Get the image dimensions and create tagz for them
                        if (ini_get('allow_url_fopen')) {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $tag_params[$tag]);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
                            $image = curl_exec($ch);
                            curl_close($ch);

                            try {
                                list($image_width, $image_height) = getimagesizefromstring($image);

                                // Only add tags if a proper image is found
                                if ($image_width !== 0 && $image_height !== 0) {
                                    $html[] = '<meta property="og:image:width" content="'. $image_width .'"/>';
                                    $html[] = '<meta property="og:image:height" content="'. $image_height .'"/>';
                                }
                            } catch (Exception $e) {
                                JLog::add('Unable to get image size.', JLog::WARNING, $e);
                            }
                        }
                    }

                    $html[] = '<meta property="'. $supported_tag .'" content="'. htmlspecialchars($tag_params[$tag]) .'"/>';
                }
            }
            if (strpos($supported_tag, "twitter:") !== false) {
                $tag = str_replace("twitter:", "twitter_", $supported_tag);
                if ((isset($tag_params[$tag])) && ($tag_params[$tag] != "")) { // TAG info found
                    if (strpos($tag, "image") !== false) { // image tag so prepend url
                        $baseUrl = JURI::root();
                        if ((strpos($tag_params[$tag], $baseUrl) === false) && (strpos($tag_params[$tag], 'http') === false)) { // Only add the base Url if it hasn't been added before or is an external image
                            $tag_params[$tag] = $baseUrl . $tag_params[$tag];
                        }
                    }

                    $html[] = '<meta name="'. $supported_tag .'" content="'. htmlspecialchars($tag_params[$tag]) .'"/>';
                }
            }
        }

        return $html;
    }

    /************************
     * isCategoryView: returns true if current view is category view
     *************************/
    private function isCategoryView($currentComponent)
    {
        $app = JFactory::getApplication();

        $currentView = $app->input->getCmd('view');
        $currentLayout = $app->input->getCmd('layout');
        $currentTask   = trim($app->input->getCmd('task', ''));

        if ($currentView == "list" || $currentView == "category" || ($currentView == "products" && $currentTask != "view") || ($currentView == "items" && $currentLayout=="blog") || $currentView == "categories" || $currentView == "featured" || $currentView == "items" || $currentView == "itemlist") {
            return true;
        } elseif ($currentComponent == "com_zoo") {
            // special things for ZOO
            $requestUrl               = $_SERVER["REQUEST_URI"];
            $regexTrailingSlashNumber = "/\\/\\d+/";

            if (((preg_match($regexTrailingSlashNumber, $requestUrl) === 1) || ($currentView == "frontpage") || ($currentTask == "category")) && ($currentTask != "item")) {
                // ZOO Category pages for some reason
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     *  Get the Component Id based on the Component Type and frontend / backend
     *
     *  @return  integer
     */
    private function getComponentId($component_type, $frontend = true)
    {
        if (!isset($component_type) || $this->isCategoryView($component_type)) {
            return false;
        }

        switch ($component_type) {
            case "com_jticketing":
                return $this->app->input->getInt('id', false);
                break;
            case "com_zoo":
                return $this->app->input->getInt('item_id', false);
                break;
            case "com_rseventspro":
                return $this->app->input->getInt('id', false);
                break;
            case "com_hikashop":
                return $this->app->input->getInt('cid', false);
                break;
            case "com_jomclassifieds":
                if ($frontend) {
                    return $this->app->input->getInt('id', false);
                } elseif (!$frontend) {
                    return $this->app->input->getInt('cid', false);
                }
                break;
            case "com_easyblog":
                if ($frontend) {
                    return $this->app->input->getInt('id', false);
                } elseif (!$frontend) {
                    return $this->app->input->getInt('uid', false);
                }
                break;
            case "com_k2":
                if ($frontend) {
                    return $this->app->input->getInt('id', false);
                } elseif (!$frontend) {
                    return $this->app->input->getInt('cid', false);
                }
                break;
            case "com_content":
                return $this->app->input->getInt('id', false);
                break;
            case "com_icagenda":
                return $this->app->input->getInt('id', false);
                break;
            case "com_phocacart":
                return $this->app->input->getInt('id', false);
                break;
            case "com_mijovideos":
                return $this->app->input->getInt('video_id', false);
                break;
            case "com_virtuemart":
                return $this->app->input->getInt('virtuemart_product_id', false);
                break;
            case "com_j2store":
                return $this->app->input->getInt('id', false);
                break;
            case "com_djclassifieds":
                return $this->app->input->getInt('id', false);
                break;
            case "com_rsblog":
                return $this->app->input->getInt('id', false);
                break;
            case "com_eshop":
                return $this->app->input->getInt('id', false);
                break;
            case "com_eventbooking":
                return $this->app->input->getInt('id', false);
                break;
            case "com_opencart":
            case "com_jcart":
                // Try this first with NON-SEF urls
                $product_id = $this->app->input->getInt('product_id', false);
                if ($product_id === false) {
                    // Opencart doesn't provide a product_id, so it is directly fetched from the SEF url
                    $tmp = substr($_SERVER["REQUEST_URI"], (strrpos($_SERVER["REQUEST_URI"], '/')+3));
                    $product_id = substr($tmp, 0, strpos($tmp, '-'));

                    if (!is_numeric($product_id)) {
                        $product_id = false;

                        $u =& JURI::getInstance(); // Get the current URL
                        if (($pos = strrpos($u, "/")) !== false) {
                            $u = substr($u, $pos+1); // only get the part after the last /
                        }

                        $db = JFactory::getDBO();

                        $query = $db->getQuery(true);
                        $query
                            ->select('query')
                            ->from($db->quoteName('oc_seo_url'))
                            ->where($db->quoteName('query') . ' LIKE ' . $db->quote('product_id=%'))
                            ->where($db->quoteName('keyword')." = ".$db->quote($u));

                        $db->setQuery($query);
                        $result = $db->loadResult();

                        if ((($pos = strpos($result, "=")) !== false) && (!is_null($result))) {
                            $product_id = intval(substr($result, $pos+1));
                        }
                    }
                }

                return $product_id;
                break;
        }
    }

    /**
     *  Get the Tag Params based on the Component Type and Component Id
     *
     *  @return array
     */
    private function getTagParams($component_type, $component_id)
    {
        if (!isset($component_type) || !isset($component_id)) {
            return false;
        }

        $component_type_short = $component_type;

        if (($pos = strpos($component_type, "_")) !== false) {
            $component_type_short = substr($component_type, $pos+1);
        }

        // Get a db connection.
        $db = $this->db;

        // Select records from the json table
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__tagz'))
            ->where($db->quoteName('component_id') . ' = '. $component_id)
            ->where($db->quoteName('component_type') . ' = "'. $component_type_short . '"');

        $db->setQuery($query);
        $params = $db->loadResult();

        if (isset($params)) {
            return json_decode($params, true);
        }

        return false;
    }

    /**
     *  Returns the og:url tag for this page
     *
     *  @return array urlTag
     */
    private function getUrlTag($customUrlTag)
    {
        $html = array();

        if ($customUrlTag != '') {
            $url = $customUrlTag;
        } else {
            $isSecure = false;

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $isSecure = true;
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
                $isSecure = true;
            }

            $url = $isSecure ? 'https' : 'http';

            $url .= "://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        $html[] = '<meta property="og:url" content="'. $url .'"/>';

        return $html;
    }

    /**
     *  Returns all Static Tag like sitename and facebook app id in an array
     *
     *  @return array staticTagz
     */
    private function getStaticTagz()
    {
        $html = array();

        foreach ($this->static_tagz as $key => $static_tag) {
            $tag_name = preg_replace('/:/', '_', $static_tag, 1);

            (strpos($static_tag, "twitter:") !== false) ? $meta = "name" : $meta = "property";

            $html[] = '<meta '. $meta .'="'. $static_tag .'" content="'. $this->params->get($tag_name) .'"/>';
        }

        return $html;
    }

    /**
     *  Search and remove tagz generated by other 3rd party extensions.
     *
     *  @return  void
     */
    private function insertTagz($tagz)
    {
        // Convert TAGZ array to string
        $markup = implode("\n\t", array_filter($tagz));

        // Return if markup is empty
        if (!$markup || empty($markup) || is_null($markup)) {
            return;
        }

        // cbs j3.x
        // $html = JResponse::getBody();
        $html = JFactory::getApplication()->getBody();
        if ($html == '') {
            return;
        }

        $tagz_html = '
<head>
	<!-- Start TAGZ: -->
	' . $markup . '
	<!-- End TAGZ -->
		';

        $html = str_replace('<head>', $tagz_html, $html);

        JFactory::getApplication()->setBody($html);
    }

    /**
     *  Search and remove tagz generated by other 3rd party extensions.
     *
     *  @return  void
     */
    private function removeOtherTagz()
    {
        $removeOtherTagz = $this->params->get('remove_other_tags');
        if (!$removeOtherTagz) {
            return;
        }

        // Get document buffer
        $body = $this->app->getBody();

        // Replace patterns
        $pattern = '/(<meta property="og:|<meta name="og:|<meta name="twitter:|<meta property="twitter:).*?\n?.*\/>/';

        // Find all matches
        preg_match_all($pattern, $body, $matches, PREG_PATTERN_ORDER);

        $result = array();

        $body = preg_replace_callback($pattern, function ($match) use (&$result) {
            $result[] = $match;
            return '';
        }, $body);

        $this->app->setBody($body);
    }

    /**
     *  Load required classes
     *
     *  @return  bool
     */
    private function loadClasses()
    {
        JLoader::register('TagzHelper', JPATH_ADMINISTRATOR . '/components/com_tagz/helpers/tagz.php');

        return class_exists('TagzHelper');
    }

    /**
     *  Loads Helper files
     *
     *  @return  boolean
     */
    private function getHelper()
    {
        // Return if helper is already loaded
        if ($this->init) {
            return true;
        }

        // Return if we are not in frontend
        if (!$this->app->isClient('site')) {
            return false;
        }

        // Only on HTML documents
        if (JFactory::getDocument()->getType() != 'html') {
            return false;
        }

        // Load required classes
        if (!$this->loadClasses()) {
            return false;
        }

        return ($this->init = true);
    }
}
