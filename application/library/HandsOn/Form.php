<?php
class HandsOn_Form extends Zend_Form
{
    const MODE_ADD      = 0;
    const MODE_EDIT     = 1;

    const LABEL_CONFIRM     = 'Salvar';
    const LABEL_CANCEL      = 'Cancelar';

    const DESCRIPTION_TEXT_ALPHA        = 'Digite apenas caracteres alfabéticos.';
    const DESCRIPTION_TEXT_ALNUM        = 'Digite apenas caracteres alfanuméricos.';
    const DESCRIPTION_TEXT_DIGITS       = 'Digite apenas caracteres numéricos.';
    const DESCRIPTION_TEXT_FLOAT        = 'Digite apenas caracteres numéricos no formato 0.00';
    const DESCRIPTION_DATE              = 'Coloque a data no formato dd/mm/aaaa (dia/mês/ano).';
    const DESCRIPTION_PASSWORD_ADD      = 'Digite apenas caracteres alfanuméricos, com no mínimo 6 caracteres.';
    const DESCRIPTION_PASSWORD_EDIT     = 'Deixe este campo em branco se você não quiser alterar a senha.';
    const DESCRIPTION_ZIP_CODE          = 'Digite apenas caracteres numéricos.';
    const DESCRIPTION_PHONE             = 'Coloque o telefone no formato (99)99999999.';
    const DESCRIPTION_PHONE_MOBILE      = 'Coloque o celular no formato (99)99999999.';
    const DESCRIPTION_PHONE_FAX         = 'Coloque o fax no formato (99)99999999.';
    const DESCRIPTION_MAIL              = 'Coloque o e-mail no formato exemplo@exemplo.com';
    const DESCRIPTION_CPF               = 'Digite apenas caracteres numéricos.';
    const DESCRIPTION_CGC               = 'Digite apenas caracteres numéricos.';
    const DESCRIPTION_SITE              = 'Coloque o site no formato http://www.exemplo.com';

    public static function descriptionUpload(array $type, $size)
    {
        return 'O arquivo deve ser do tipo ' . implode(" ou ", $type) . ' e ter no máximo ' . $size;
    }

    protected $_mode = self::MODE_ADD;

    protected $_id = null;

    protected $_hash = true;

    protected $_dynamicGroup = array();

    protected $_elementDecorators = array(
        array('ViewHelper'),
        array('Errors'),
        array('Description', array('tag' => 'div', 'class'=>'hint')),
        array(array('elementTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement')),
        array('Label', array('class' => 'formLabel', 'requiredSuffix' => ':*', 'optionalSuffix' => ':')),
        array(array('rowTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow')));

    protected $_buttonElementDecorators = array(
        array('ViewHelper'),
        array('Errors'),
        array(array('elementTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formElement')),
        //array('Label', array('class' => 'formLabel', 'requiredPrefix' => ' *')),
        array(array('rowTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow')));

    protected $_noElementDecorator = array('ViewHelper');

    protected $_groupDecorators = array('FormElements', 'Fieldset');

    public function __construct($options = null)
    {
        $this->addElementPrefixPath('HandsOn_Form_Decorator', 'HandsOn/Form/Decorator/', 'decorator');
        $this->addElementPrefixPath('HandsOn_Filter', 'HandsOn/Filter/', 'filter');
        $this->addElementPrefixPath('HandsOn_Validate', 'HandsOn/Validate/', 'validate');
        $this->addPrefixPath('HandsOn_Form_Element', 'HandsOn/Form/Element/', 'element');

        $this->_setupTranslation();

        if (is_array($options)) {
            if (isset($options['id'])) {
                $this->_mode = self::MODE_EDIT;
                $this->_id = $options['id'];
                unset($options['id']);
            }
            if (isset($options['hash'])) {
                $this->_hash = ($options['hash'] == false) ? false : true;
                unset($options['hash']);
            }
        }

        parent::__construct($options);
        
        $id = get_class($this);
        $id[0] = strtolower($id[0]);
        $this->setAttrib('accept-charset', 'UTF-8')
             ->setAttrib('id', $id);

        if ($this->_hash !== true) {
            $this->addElement('hash', 'formId', array(
                'attribs'       => array('class' => 'formElementHidden'),
                'decorators'    => array('ViewHelper'),
                'salt'          => $this->getId()
            ));
        }
    }

    protected function _setupTranslation()
    {
        if (self::getDefaultTranslator()) {
            return;
        }
        $path = APPLICATION_PATH . '/languages/pt_BR.php';
        $translate = new Zend_Translate('array', $path, 'en');
        self::setDefaultTranslator($translate);
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('Form', array('class' => 'structured'));
        }
    }

    public function addElement($element, $name = null, $options = null)
    {
        if (!isset($options['attribs']['class'])) {
            if (is_string($element)) {
                $options['attribs']['class'] = 'formElement' . ucfirst($element);
            } else {
                $type = substr($element->getType(), strrpos($element->getType(), '_') + 1);
                $options['attribs']['class'] = 'formElement' . $type;
            }
        }
        if (!isset($options['decorators']) && $element != 'submit') {
            $options['decorators'] = $this->_elementDecorators;
        }
        if ($element == 'button') {
            $options['decorators'] = $this->_buttonElementDecorators;
        }
        return parent::addElement($element, $name, $options);
    }

    public function addDisplayGroup(array $elements, $name, $options = null)
    {
        if (!isset($options['decorators'])) {
           $options['decorators'] = $this->_groupDecorators;
        }
        return parent::addDisplayGroup($elements, $name, $options);
    }

    public function addDynamicGroup(array $elements, $name, $options = null, array $elementsHeader = null)
    {
//        print_r($elementsHeader);
//        exit();
        
        $name = (string) $name;
        if (empty($name)) {
            throw new Zend_Form_Exception('Nome inválido para grupo dinâmico');
        }

        foreach ($elements as $elementName) {
            $element = $this->getElement($elementName);
            if ($element != null) {
                $required = $element->isRequired();
                $this->_dynamicGroup[$name][$elementName] = $required;
                if ($required == true) {
                    $element->setRequired(false);

                    $elementTag = $element->getDecorator('elementTag');
                    if ($elementTag != null) {
                        $elementTag->setOption('class', 'formElement required');
                    }
                }
            }
        }

        $options['class'] = 'dynamicGroup';

        $hidden = $name . 'DynamicValues';
        $this->addElement('hidden', $hidden, array(
            'decorators' => array('ViewHelper')
        ));
        $elements[] = $hidden;

        if (isset($options['values']) && !empty($options['values'])) {
            $this->setDynamicGroupValues($name, $options['values']);
            unset($options['values']);
        }
//        print_r($elements);
//        exit();
        return $this->addDisplayGroup($elements, $name, $options, $elementsHeader);
    }

    public function setDynamicGroupValues($name, $values)
    {
        $hiddenValues = $this->getElement($name . 'DynamicValues');
        if (!empty($hiddenValues)) {
            $values = Zend_Json::encode($values);
            $hiddenValues->setValue($values);
        }
    }

    public function getValues($suppressArrayNotation = false)
    {
        $values = parent::getValues($suppressArrayNotation);
        /*
        foreach ($this->_dynamicGroup as $group => $fields) {
            $dynamicGroupValues = Zend_Json::decode($values[$group . 'DynamicValues']);
            if (is_array($dynamicGroupValues)) {
                $expandedValues = array();
                foreach ($dynamicGroupValues as $line => $columns) {
                    $expandedValues[$line] = array();
                    $i = 0;
                    foreach ($fields as $name => $required) {
                        $expandedValues[$line][$name] = $columns[$i++];
                    }
                }
                $values[$group . 'DynamicValues'] = $expandedValues;
            }
        }
        */
        return $values;
    }

    public function isValid($data)
    {
        $valid = true;
        foreach ($this->_dynamicGroup as $group => $fields) {
            $dynamicGroupValues = Zend_Json::decode($data[$group . 'DynamicValues']);
            if (is_array($dynamicGroupValues)) {
                foreach ($dynamicGroupValues as $line => $values) {
                    $i = 0;
                    foreach ($fields as $field => $required) {
                        if ($required == true && empty($values[$i])) {
                            // Dado obrigatório não preenchido
                        }

                        $element = $this->getElement($field);
                        $valid = $element->isValid($values[$i]) && $valid;
                        $element->clearErrorMessages()->setValue(null);
                        $i++;
                    }
                }
            }
        }
        return parent::isValid($data) && $valid;
    }

    public function addSubmit($confirmLabel = self::LABEL_CONFIRM)
    {
        $this->addElement('submit', 'confirm', array(
            'decorators'    => $this->_buttonElementDecorators,
            'label'         => $confirmLabel));
    }
}