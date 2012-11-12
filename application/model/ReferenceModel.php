<?php

/**
 * Description of ReferenceModel
 *
 * @author Michael Streb <michael.streb@michael-streb.de>
 */
class ReferenceModel extends AbstractModel
{
    /**
     * Holds all loaded user models.
     * @var array
     */
    public static $_instances    = array();

    /**
     * The table that holds teh data for the UserModel.
     * @var string
     */
    protected static $_sTable   = Db_MichaelStreb_Config::TABLE_REFERENCE;

    /**
     * Contains all data reffered to a singel user.
     * @var array
     */
    protected $_aData   = array(
        'reference_id'  => null,
        'name'          => null,
        'url'           => null,
        'time_insert'   => null,
        'time_update'   => null,
    );
    
    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField    = 'reference_id';

    /**
     * Contains regular expression to validate user data. If a user value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
        'url'   => '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'
    );
    
    /**
     * The accepted mime types for file uploads.
     * @var array
     */
    protected $_aAcceptedImageMimeTypes = array('image/jpg', 'image/png', 'image/gif');

    protected function __construct($mData) {
        parent::__construct($mData);
    }
    
    /**
     * Checks wether teh current instance can be deleted or not.
     * @param   int     $iId    The id of the model to delete.
     * @return  bool
     */
    protected static function _isDeleteAllowed($iId) {
        return true;
    }
    
    #################
    ## GET METHODS ##
    #################
    
    /**
     * Returns the reference name.
     * @return str
     */
    public function getName() {
        return $this->_aData['name'];
    }
    
    /**
     * Returns the reference url.
     * @return str
     */
    public function getUrl() {
        return $this->_aData['url'];
    }
    
    /**
     * Returns an image for the reference for the given index.
     * @param   int $iIndex The index of the image.
     * @return  ReferenceImageModel
     */
    public function getImage($iIndex = 0) {
        $sImageFilePath = PATH_IMAGES . 'reference/' . $this->getId() . '_' . $iIndex . '.jpg';
        if (file_exists($sImageFilePath)) {
            $aImageInfo         = getimagesize($sImageFilePath);
            renameArrayIndex($aImageInfo, array(0, 1, 2, 3), array('width', 'height', 'typeFlag', 'attributeString'));
            $aImageInfo['path'] = $sImageFilePath;
            return $aImageInfo;
        }
        return false;
    }


    #################
    ## SET METHODS ##
    #################
    
    /**
     * Sets the reference name.
     * @param   str     $value  The reference name.
     * @return  bool
     */
    public function setName($value) {
        if (
            (isset($this->_aDataValidation['name']) && preg_match($this->_aDataValidation['name'], $value))
            || !isset($this->_aDataValidation['name'])
        ) {
            $this->_aData['name'] = $value;
            return true;
        }
        return false;
    }
    
    /**
     * Sets the reference url.
     * @param   str     $value  The reference url.
     * @return  bool
     */
    public function setUrl($value) {
        if (
            (isset($this->_aDataValidation['url']) && preg_match($this->_aDataValidation['url'], $value))
            || !isset($this->_aDataValidation['url'])
        ) {
            $this->_aData['url'] = $value;
            return true;
        }
        return false;
    }
    
    ###################
    ## OTHER METHODS ##
    ###################
    
    
    public function saveImage($aImageInfo) {
        var_dump($aImageInfo);

        $oImage = new Imagick($aImageInfo['tmp_name']);
        echo $oImage->resizeimage(1024, 768, 0, 0, true);
        $oImage->writeimage(PATH_IMAGES . 'references/' . $this->_aData['reference_id'] . '.jpg');
        die();
        
        if (!in_array($aImageInfo['type'], $this->_aAcceptedImageMimeTypes)) {
            Mjoelnir_Redirect::redirect('/reference/index/message/2000');
        }
    }
}