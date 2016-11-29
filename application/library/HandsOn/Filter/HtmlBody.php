<?php
//class HandsOn_Filter_HtmlBody extends HandsOn_Filter_HTMLPurifier
class HandsOn_Filter_HtmlBody implements Zend_Filter_Interface
{
/*    public function __construct($newOptions = null)
    {
        $options = array(
            array('Cache', 'SerializerPath', APPLICATION_PATH . '/../data/cache/htmlpurifier'),
            array('HTML', 'Doctype', 'XHTML 1.0 Strict'),
            array('HTML', 'Allowed',
                'h3, h4, p, em, i, strong, b, a[href], ul, ol, li, img[src|alt|height|width], sub, sup, br'
            ),
            array('AutoFormat', 'Linkify', 'true'),
            array('AutoFormat', 'AutoParagraph', 'true')
        );

        if (!is_null($newOptions)) {
            // I'll let HTMLPurifier overwrite original options
            // with new ones rather than filter them myself
            $options = array_merge($options, $newOptions);
        }
        
        parent::__construct($options); 
    }*/
    
    public function filter($value)
    {
        //return strip_tags($value, '<p><a><img><ul><ol><li><h3><h4><b><strong><i><em>');
        return $value;
    }

}