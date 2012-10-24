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
     * The title of the page.
     * @var str
     */
    protected $_sPageTitel  = '';

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
     * Debug content to display at the bottom of the page.
     * @var array
     */
    protected $_debugContent    = array();

    protected function __construct($request) {
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
     * Returns the name of the appilcation.
     * @return  str
     */
    public function getApplicationName() {
        return APPLICATION_NAME;
    }


    /**
     * sets a new baseTemplate for all pages using this object instance
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

    /**
     * Builds the page and sends it to the client.
     */
    public function run() {
        $oAcl       = Mjoelnir_Acl::getInstance();
        $oBootstrap = new Bootstrap();
        $oLog       = Mjoelnir_Log::getInstance();
        
        try {
            if (UserModel::getCurrentUser() !== false) {
                // Authorized user
                if (
                    !$oAcl->isAllowed(strtolower(APPLICATION_NAME), strtolower($this->_page), strtolower($this->_action))
                ) {
                    $oLog->log('The user tried to access a not defined permission.');

                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('error' => true, 'status' => 403, 'message' => Mjoelnir_Message::getMessage(2010)));
                        exit();
                    }

                    header('Location: ' . WEB_ROOT . 'error/forbidden');
                    exit();
                }
            }
            else {
                // Not authorized user
                if ($this->_page != $this->_defaultPage || $this->_action != $this->_defaultAction) {
                    if (RETURN_METHOD == 'json') {
                        echo json_encode(array('error' => true, 'status' => 401, 'message' => Mjoelnir_Message::getMessage(2009)));
                        exit();
                    }

                    header('Location: ' . WEB_ROOT . '' . $this->_defaultPage . '/' . $this->_defaultAction);
                    exit();
                }
            }
        }
        catch (Mjoelnir_Acl_Exception $e) {
            $oLog->log('The user tried to access a not defined permission.');
            header('Location: ' . WEB_ROOT . 'error/forbidden');
            exit();
        }

        $oBootstrap->start();

        $oMessages  = Mjoelnir_Message::getInstance(Mjoelnir_Request::getInstance());

        $methodName = $this->_action . 'Action';
        $content    = $this->_pageObj->$methodName();

        $view   = new Mjoelnir_View();
        $view->setTemplateDir(PATH_TEMPLATE);

        $view->assign('baseUrl', (preg_match('/HTTP\//', $_SERVER['SERVER_PROTOCOL'])) ? 'http://' . $_SERVER['HTTP_HOST'] : 'https://' . $_SERVER['HTTP_HOST']);
        $view->assign('headTitle', $this->_sPageTitel);
        $view->assign('applicationEnv', APPLICATION_ENV);
        $view->assign('WEB_ROOT', WEB_ROOT);
        $view->assign('css', implode("\n", $this->_css));
        $view->assign('jsHeader', implode("\n", $this->_javascript['header']));
        $view->assign('jsFooter', implode("\n", $this->_javascript['footer']));
        $view->assign('breadcrumb', $this->_breadcrumbPaths);
        $view->assign('aMessages', $oMessages->getAllMessages());
        $view->assign('CONTENT', $content);
        $view->assign('oAcl', $oAcl);
        $view->assign('oCurrentUser', UserModel::getCurrentUser());

        $oBootstrap->end();

        $view->assign('debugContent', implode("\n", $this->_debugContent));

        if (RETURN_METHOD == 'json') {
            echo json_encode(array('status' => 200, 'page' => '/' . $this->_page . '/' . $this->_action));
            die();
        }
        else {
            $view->display($this->_baseTempalte);
        }
    }
    
    /**
     * Sets the page title.
     * @param   str $sPageTitel The title of the page.
     * @return  bool
     */
    public function setPageTitle($sPageTitel) {
        $this->_sPageTitel  = $sPageTitel;
        return true;
    }

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
