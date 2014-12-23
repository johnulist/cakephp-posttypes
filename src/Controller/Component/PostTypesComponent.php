<?php

namespace PostTypes\Controller\Component;

use Cake\Controller\Component;
use Cake\Utility\Inflector;

/**
 * PostTypes component
 */
class PostTypesComponent extends Component
{

    public function __construct($registry, array $config = array()) {
        parent::__construct($registry, $config);

        $this->Controller = $this->_registry->getController();
    }

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'formFieldOptions' => [
        ],
        'listFieldOptions' => [
            'hide'   => false,
            'get'    => false,
            'before' => '',
            'after'  => '',
        ]
    ];
    protected static $_postTypes = [];

    public function startup($event) {

    }

    /**
     * Registers a new posttype
     *
     * @param string $name of the type
     * @param array $options for the type
     *
     * ### OPTIONS
     * - menu   boolean     set if you want a menu-item for the admin-area
     * - model  string      is the model-name to use
     * - fields mixed       use an array to define the fields to use, set to false to use the postTypeFields-method in your table-class
     * - alias  string      the alias
     *
     */
    public function register($name, $options = []) {

        $_options = [
            'menu'        => false,
            'model'       => ucfirst($name),
            'contain'     => [],
            'tableFields' => false,
            'formFields'  => false,
            'alias'       => $name,
            'name'        => ucfirst($name),
            'type'        => Inflector::singularize(ucfirst($name)),
        ];

        $name = ucfirst($name);

        $options = array_merge($_options, $options);

        // We have to map the fields-array if it's not false
        if ($options['tableFields']) {
            $options['tableFields'] = $this->maptableFields($options['tableFields']);
        }

        // We have to map the fields-array if it's not false
        if ($options['formFields']) {
            $options['formFields'] = $this->mapFormFields($options['formFields']);
        }

        // Adding menu-items if set
        if ($options['menu']) {
            $this->_addMenu($name, $options);
        }

        $list = self::$_postTypes;

        $list[$name] = $options;

        self::$_postTypes = $list;
    }

    /**
     * Checks if the given posttype is registerd
     *
     * @param string $name of the posttype
     * @return bool if the type exists
     */
    public function check($name) {

        $name = ucfirst($name);

        return(key_exists($name, self::$_postTypes));
    }

    /**
     * Returns the options of the posttype
     * If the posttype is not set the method will return bool false
     *
     * @param string $name
     * @return boolean
     */
    public function get($name) {

        $name = ucfirst($name);

        if ($this->check($name)) {
            return self::$_postTypes[$name];
        }
        return false;
    }

    /**
     * Maps the given list-field-list
     * @param type $name
     * @param type $options
     */
    public function maptableFields($fields) {

        $_fields = [];

        foreach ($fields as $key => $options) {

            $_options = $this->config('listFieldOptions');

            if (is_array($options)) {

                $_fields[$key] = array_merge($_options, $options);
            } else {

                $_fields[$options] = $_options;
            }
        }

        return $_fields;
    }

    /**
     * Maps the given form-field-list
     * @param type $fields
     * @return type
     */
    public function mapFormFields($fields) {

        $_fields = [];

        foreach ($fields as $key => $options) {

            $_options = $this->config('formFieldOptions');

            if (is_array($options)) {

                $_fields[$key] = array_merge($_options, $options);
            } else {

                $_fields[$options] = $_options;
            }
        }

        return $_fields;
    }

    protected function _addMenu($name, $options) {

        if (key_exists('prefix', $this->Controller->request->params) AND $this->Controller->request->params['prefix'] == 'admin') {
            $this->Controller->Menu->add($options['alias'], [
                'url' => [
                    'prefix'     => 'admin',
                    'plugin'     => 'PostTypes',
                    'controller' => 'post_types',
                    'action'     => 'index', $name
                ]
            ]);
        }
    }

}
