<?php

/**
 * Die Site-Klasse generiert die gew�nschte Seite und gibt diese zur�ck.
 *
 * @author Michael Streb
 * @since 28.02.2008
 *
 */
class Mjoelnir_Site
{
    /**
     * Singelton instance of class Site.
     * @var Site
     */
    private static $instance;

    /**
     * The base url.
     * @var string
     */
    protected $_baseUrl  = null;

    /**
     * The baseTemplate for all pages
     * @var string
     */
    protected $_baseTempalte = 'layout.tpl.html';

    /**
     * The page to render.
     * @var string
     */
    protected $_page    = null;

    /**
     * If no page is given in the url, the default page is used.
     * @var string
     */
    protected $_defaultPage = 'index';

    /**
     * The page object.
     * @var AbstractPage
     */
    protected $_pageObj = null;

    /**
     * The action to execute on the page.
     * @var string
     */
    protected $_action  = null;

    /**
     * If no action is givven
     * @var type
     */
    protected $_defaultAction   = 'index';

    /**
     * Parameters geiven to the page.
     * @var array
     */
    protected $_params  = array();

    /**
     * Javascript to add into the layout template.
     * @var array
     */
    protected $_javascript  = array('header' => array(), 'footer' => array());

    /**
     * Css too add into the layout template.
     * @var array
     */
    protected $_css = array();

    /**
     * The breadcrumb paths.
     * @var array
     */
    protected $_breadcrumbPaths  = array();

    /**
     * The page title.
     * @var str
     */
    protected $_sPageTitle  = '';

    /**
     * Debug content to display at the bottom of the page.
     * @var array
     */
    protected $_debugContent    = array();

    protected function __construct() {
        // Overwrite default page and action settings if config parameters are set.
        if (defined(DEFAULT_PAGE))      { $this->_defaultPage = DEFAULT_PAGE; }
        if (defined(DEFAULT_ACTION))    { $this->_defaultAction = DEFAULT_ACTION; }
        
        /**
         * Set page.
         */
        try {
            $this->setPage(\Mjoelnir_Request::getParameter('page', $this->_defaultPage));

            /**
             * Set action.
             */
            try {
                $this->setAction(\Mjoelnir_Request::getParameter('action', $this->_defaultAction));
            }
            catch (Exception $e) {
                echo 'Ein Fehler verhindert das die gew&uuml;nschte Seite angezeigt werden kann (' . $e->getMessage() . ').';
            }
        }
        catch (Exception $e) {
            echo 'Ein Fehler verhindert das die gew&uuml;nschte Seite angezeigt werden kann (' . $e->getMessage() . ').';
        }
    }

    /**
     * Returns a singleton instance of class Site.
     * @param array $request
     * @return Site
     */
    public static function getInstance($request = array()) {
        if (is_null(self::$instance)) {
            self::$instance = new Mjoelnir_Site($request);
        }

        return self::$instance;
    }

    /**
     * Builds the page and sends it to the client.
     */
    public function run() {
        $methodName = $this->_action . 'Action';
        $view       = $this->_pageObj->$methodName();
        
        $view->assign('headTitle', $this->_getPageTitle());
        $view->assign('css', implode("\n", $this->_css));
        $view->assign('jsHeader', implode("\n", $this->_javascript['header']));
        $view->assign('jsFooter', implode("\n", $this->_javascript['footer']));
        $view->assign('breadcrumb', $this->_breadcrumbPaths);
        $view->assign('debugContent', implode("\n", $this->_debugContent));

        return $view;
    }
    
    #################
    ## GET METHODS ##
    #################
    
    /**
     * Returns the name of the appilcation.
     * @return  str
     */
    public function getApplicationName() {
        return APPLICATION_NAME;
    }
    
    /**
     * Returns thr requested page.
     * @return str
     */
    public function getPage() {
        return $this->_page;
    }

    /**
     * Returns the requested action.
     * @return str
     */
    public function getAction() {
        return $this->_action;
    }
    
    /**
     * Returns the default page.
     * @return str
     */
    public function getDefaultPage() {
        return $this->_defaultPage;
    }
    
    /**
     * Returns the default action.
     * @return str
     */
    public function getDefaultAction() {
        return $this->_defaultAction;
    }

    /**
     * Returns the page title.
     * @return str
     */
    protected function _getPageTitle() {
        if (strlen($this->_sPageTitle) > 0) {
            return implode(' ', array(PAGE_TITLE_PREFIX, PAGE_TITLE_GLUE, $this->_sPageTitle));
        }
        return PAGE_TITLE_PREFIX;
    }

    /**
     * Returns the content for the request.
     */
    public function display($view) {
        if (RETURN_METHOD == 'json') {
            echo json_encode(array('status' => 200, 'page' => '/' . $this->_page . '/' . $this->_action));
            die();
        }
        else {
            $sTemplateInheritance   = 'extends:' . $this->_baseTempalte . '|' . $view->getTemplate();
            $view->display($sTemplateInheritance);
        }
    }

    #################
    ## SET METHODS ##
    #################
    
    /**
     * Sets a new baseTemplate for all pages using this object instance
     */
    public function setBaseTemplate($template = null) {
        if (!is_null($template)) {
            $this->_baseTempalte = $template;
        }
    }

    /**
     * Set the page parameter.
     * @param string $page The called page
     * @return bool
     */
    public function setPage($page = null) {
        $className      = ucfirst($page) . 'Controller';
        if (!is_null($page) && strlen($page) > 0) {
            if (file_exists(PATH_CONTROLLER . '/' . $className . '.php')) {
                $this->_page    = $page;

                $sQualifiedClassName    = APPLICATION_NAME . '\\' . $className;

                $this->_pageObj = new $sQualifiedClassName();

                return true;
            }
            else {
                if ($page != $this->_defaultPage) {
                    $this->setPage($this->_defaultPage);
                }
                else {
                    throw new Exception('No page found to load.');
                }
            }
        }
        else {
            $this->setPage($this->_defaultPage);
        }

        return false;
    }
    
    /**
     * Sets the default page.
     * @param   str $sPage
     */
    public function setDefaultPage($sPage) {
        $this->_defaultPage = $sPage;
    }

    /**
     * Sets the action parameter.
     * @param string $action
     * @return bool
     */
    public function setAction($action = null) {
        if (is_null($this->_pageObj)) {
            throw new Exception('No page object given.');
        }

        if (!is_null($action) && strlen($action) > 0) {
            $methodName = $action . 'Action';
            if (method_exists($this->_pageObj, $methodName)) {
                $this->_action    = $action;
            }
            else {
                if ($action != $this->_defaultAction) {
                    $this->setAction($this->_defaultAction);
                }
                else {
                    throw new Exception('Action not found: ' . $this->_page . '::' . $action);
                }
            }
        }
        else {
            $this->setAction($this->_defaultAction);
        }

        return true;
    }
    
    /**
     * Sets the default action.
     * @param   str $sAction
     */
    public function setDefaultAction($sAction) {
        $this->_defaultAction = $sAction;
    }
    
    public function setPageTitle($sTitle) {
        $this->_sPageTitle  = $sTitle;
    }

    ###################
    ## OTHER METHODS ##
    ###################
    
    /**
     * Adds a css file.
     * @param   str $css    The path to the css file relative to the the css path.
     * @return  bool
     */
    public function addCssFile($css) {
        if (strlen($css) > 0 && file_exists(DOCUMENT_ROOT . PATH_CSS . $css)) {
            $this->_css[]   = '<link rel="stylesheet" type="text/css" href="' . WEB_ROOT . PATH_CSS . $css . '" />';
            return true;
        }

        return false;
    }

	/**
     * removes a css file.
     * @param   str $css    The path to the css file relative to the the css path.
     * @return  bool
     */
    public function removeCssFile($css) {
		if (strlen($css) > 0) {
			$sCss = '<link rel="stylesheet" type="text/css" href="' . WEB_ROOT . PATH_CSS . $css . '" />';
			$pos = array_search($sCss, $this->_css);
			if ($pos !== false) {
				unset($this->_css[$pos]);
				return true;
			}
            return false;
        }
        return false;
    }

    /**
     * Adds a css string to the given location.
     * @param   str $css        The css string.
     * @return  bool
     */
    public function addCssString($css, $location = 'header') {
        if (array_key_exists($location, $this->_css)) {
            $this->_css[]   = $css;
            return true;
        }

        return false;
    }

    /**
     * Adds a javascript file to the given location.
     * @param   str $js         The path to the javascript file relative to the the js path.
     * @param   str $location   The location to add the file to.
     * @return  bool
     */
    public function addJsFile($js, $location = 'footer') {
        if (strlen($js) > 0 && file_exists(DOCUMENT_ROOT . PATH_JS . $js) && array_key_exists($location, $this->_javascript)) {
            $this->_javascript[$location][]   = '<script type="text/javascript" src="' . WEB_ROOT . PATH_JS . $js . '"></script>';
            return true;
        }

        return false;
    }

    /**
     * Adds a javascript string to the given location.
     * @param   str $js         The javascript string.
     * @param   str $location   The location to add the string to.
     * @return  bool
     */
    public function addJsString($js, $location = 'footer') {
        if (array_key_exists($location, $this->_javascript)) {
            $this->_javascript[$location][]   = $js;
            return true;
        }

        return false;
    }

    /**
     * Adds one or more paths to the breadcrumb.
     * @param   array   $aPath  An array containing one or more key value pairs.
     * @return  bool
     */
    public function addBreadcrumb($aPath) {
        $this->_breadcrumbPaths[] = $aPath;
        return true;
    }

    /**
     * Adds debug content to the page bottom.
     * @param   str $string The string to display.
     * @return  bool
     */
    public function addDebugContent($string) {
        if (strlen($string)) {
            $this->_debugContent[]  = $string;
            return true;
        }

        return false;
    }
}
