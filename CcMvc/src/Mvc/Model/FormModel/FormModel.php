<?php

namespace Cc\Mvc;

use Cc\Mvc;
use Cc\ValidDependence;
use Cc\ValidDefault;
use Cc\Inyectable;

/**
 * clase base para modelos de formularios html 
 * 
 * 
 * EL METODO Campos DEBE REOTRNAR UN ARRAY CON LOS NOMBRES DE LOS CAMPOS COMO INDICES  Y Y UN ARRAY COMO VALOR
 * QUE DEBE CONTENER EN EL INDICE  0 EL TIPO DE CAMPO HTML EJEMPLO PARA TEXTO NORMAL text O UN CAMPO DE CORREO email
 * EN INDICE 1 ES OPCIONAL Y DEBE CONTENER EL VALOR POR DEFECTO DEL CAMPO,
 * EL INDIICE 3 TAMBIEN ES OPCIONAL Y DEBE CONTENER LA IFORMACION PARA LA VALIDACION GENERADA CON ALGUNA DE LAS CLASES DE VALIDACION 
 * EXTENDIDAS DE {@link ValidDependence} Y EL METODO {@link ValidDependence::CreateValid}
 * EJEMPLO:
 * <code>
 * <?php
 * namespace Cc\Mvc;
 * class MyFormulario extends FormModel{
 *      protected function Campos()
 *      {
 *           $campos=[
 *                  'campo'=>[FormTypeHtml,DefultValue,typevalid],
 *                  'campo1'=>[FormTypeHtml,DefultValue,typevalid],
 *                  .       
 *                  .                                               
 *                  .
 *                  ];
 *           return $campos;
 *      }
 * }
 * </code>
 * Un ejemplo mas completo:
 * <code>
 * <?php
 * namespace Cc\Mvc;
 * class MyFormulario extends FormModel
 * {
 *      protected function Campos()
 *      {
 *              $campos=[
 *                      'nombre' => ['text','',['required'=>true]],
 *                      'apellido' => ['text','',],
 *                      'correo' => ['email'],
 *                      'telefono' => ['tel'],
 *                      'websitie'=>['text','',ValidUrl::CreateValid()],
 *                      'unaIp'=> ['text','127.0.0.1',ValidIp::CreateValid()],
 *                      'pass1' => ['password'],
 *                      'pass2' => ['password']
 *                  ];
 *          return $canpos;
 *      }
 * }
 * </code>
 * 
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Modelo
 * @category FormModel
 */
abstract class FormModel extends Model implements Inyectable, \Serializable
{

    /**
     * <code>
     * 'name'=>[type,DefultValue,typevalid]
     * </code>
     * @var array 
     */
    private $campos = [];

    /**
     * metodo de trasmision de datos 
     * @var string 
     */
    private $Method = 'POST';
    private $existFile = false;
    private $NameSubmited;
    private $Sumited = false;
    private static $count = 0;
    protected $valid = false;
    protected $action = '';
    protected $protected = false;
    private $UseCache = true;

    const TypeHtml = 0;
    const DefaultConten = 1;
    const Validate = 2;

    private $inyected = false;
    protected $RequestUri = '';

    public static function CtorParam()
    {
        return [NULL, 'POST', true, true];
    }

    /**
     * methodo que se utilizara para trasmitir los datos puede ser _POST o _GET
     * @param string $method
     */
    public function __construct($action = NULL, $method = 'POST', $protected = true, $inyected = false)
    {
        $this->Method = $method;
        $this->NameSubmited = 'Submited' . static::class . self::$count;
        self::$count++;
        $this->protected = $protected;
        $this->inyected = $inyected;
        $this->RequestUri = Mvc::App()->Request->Uri();
        if (!is_null($action))
        {
            $this->action = $action;
        }
        if (!$inyected)
        {
            $this->Request();
        }
    }

    public function serialize()
    {
        $seri = [
            'campos' => $this->campos,
            'Method' => $this->Method,
            'action' => $this->action,
            'NameSubmited' => $this->NameSubmited,
            'lastPage' => $this->RequestUri,
            '_ValuesModel' => $this->_ValuesModel,
            'existFile' => $this->existFile
        ];
        return serialize($seri);
    }

    public function unserialize($serialized)
    {
        $serialized = unserialize($serialized);
        $this->campos = $serialized['campos'];
        $this->Method = $serialized['Method'];
        $this->action = NULL;
        if (!preg_match("/(" . preg_quote($serialized['action'], '/') . ")/i", Mvc::App()->Request->Url()))
        {
            $process = false;
        } else
        {
            $process = $serialized['lastPage'] == Mvc::App()->Request->Refere();
        }
        $this->inyected = false;
        $this->NameSubmited = $serialized['NameSubmited'];
        $this->protected = $serialized['lastPage'] == Mvc::App()->Request->Uri() || $serialized['lastPage'] != Mvc::App()->Request->Refere();
        $this->inyected = false;
        $this->_ValuesModel = $serialized['_ValuesModel'];
        $this->existFile = $serialized['existFile'];
        $this->RequestUri = Mvc::App()->Request->Uri();
        ;
        $this->Request(true, $process);

        //return $this;
    }

    public function &Method($method = NULL)
    {
        if (is_null($method))
        {
            $this->Method = $method;
        }
        return $this;
    }

    public function &Action($action)
    {
        $this->action = $action;
        return $this;
    }

    public function &ProtectedUrl($protected)
    {
        if (is_null($protected))
        {
            $this->protected = $protected;
        }
        return $this;
    }

    private function Request($serialized = false, $process = true)
    {
        $this->inyected = false;

        if (!$serialized)
            $this->LoadMetaData();

        if ($process)
        {
            $this->ProcessSubmit();
            if ($this->IsSubmited() && !$this->IsValid())
            {
                foreach ($this->campos as $i => $v)
                {
                    if ($v[self::TypeHtml] != 'password' && $this->offsetExists($i))
                        $this->campos[$i][self::DefaultConten] = $this->offsetGet($i);
                }
            }
        }
    }

    protected function campos()
    {
        $reflexion = new \ReflectionClass($this);
        $prop = $reflexion->getProperties(\ReflectionProperty::IS_PUBLIC);
        $array = [];
        /* @var $propiedad \ReflectionProperty */
        foreach ($prop as $i => $propiedad)
        {
            if ($propiedad->getDeclaringClass()->name == $reflexion->name)
            {
                $name = $propiedad->getName();
                $array[$name] = $propiedad->getValue($this);
                unset($this->{$name});
            }
        }
        return $array;
    }

    private function LoadMetaData()
    {
        foreach ($this->campos() as $i => $v)
        {
            if ($v[self::TypeHtml] == 'file')
            {
                if (!is_array($this->existFile))
                {
                    $this->existFile = [];
                }

                array_push($this->existFile, $i);
            }


            if (!isset($v[self::Validate]) || (is_array($v[self::Validate]) && !isset($v[self::Validate][ValidDependence::class])))
            {

                if (isset($v[self::Validate]) && is_array($v[self::Validate]) && (!isset($v[self::Validate][ValidDependence::class]) ))
                {
                    $options = $v[self::Validate] + ['required' => false];
                } else
                {
                    $options = ['required' => false];
                }
                if (!isset($v[self::Validate]))
                {
                    $v[self::Validate] = [];
                }
                if (preg_match('/\w\[\]/', $v[self::TypeHtml]))
                {


                    $item = $this->ParseValid([], str_replace('[]', '', $v[self::TypeHtml]), ['required' => true] + $options);
                    $v[self::Validate] = ValidArray::CreateValid(['ValidItems' => $item] + $options);
                } else
                {
                    $v[self::Validate] = $this->ParseValid($v[self::Validate], $v[self::TypeHtml], $options);
                }
            }

            $this->campos[$i] = $v;
            $this->_ValuesModel[$i] = '';
        }
    }

    protected function ParseValid($v, $type, $options)
    {
        switch (strtolower($type))
        {
            case 'number':
            case 'range':
                $v = ValidNumber::CreateValid($options);
                break;
            case 'email':
                $v = ValidEmail::CreateValid($options);
                break;
            case 'tel':
                $v = ValidTelf::CreateValid($options);
                break;
            case 'color':
                $v = ValidExadecimal::CreateValid($options);
                break;
            case 'date':
                $v = ValidDate::CreateValid(['format' => 'Y/m/d'] + $options);
                break;
            case 'datetime':
                $v = ValidDate::CreateValid(['format' => 'Y/m/d H:i:s'] + $options);
                break;
            case 'week':
                $v = ValidDate::CreateValid(['format' => 'Y-W'] + $options);
                break;
            case 'month':
                $v = ValidDate::CreateValid(['format' => 'Y-m'] + $options);
                break;
            case 'time':
                $v = ValidDate::CreateValid(['format' => 'H:i:s'] + $options);
                break;
            case 'year':
                $v = ValidDate::CreateValid(['format' => 'Y'] + $options);
                break;
            case 'datetime-local':
                $v = ValidDate::CreateValid(['format' => \DateTime::W3C] + $options);
                break;
            case 'url':
                $v = ValidUrl::CreateValid($options);
                break;
            case 'file':
                //case 'image':
                $v = ValidString::CreateValid(['opt_files' => $options, 'required' => false]);
                break;
            default :

                $v = ValidString::CreateValid($options);
        }
        return $v;
    }

    /**
     * Establece el valor por defecto de campo en el formulario
     * @param string $name
     * @param string $value
     */
    public function DefaultValue($name, $value = NULL)
    {
        if (is_array($name) || $name instanceof \Traversable)
        {
            foreach ($name as $i => $v)
            {
                $this->DefaultValue($i, $v);
            }
            return;
        }

        if (key_exists($name, $this->campos))
        {
            $this->campos[$name][self::DefaultConten] = $value;
        }
    }

    /**
     * obtiene el valor por defecto del campo en el formulario
     * @param string $name
     * @return mixes
     */
    public function GetDefaultValue($name)
    {
        if (isset($this->campos[$name][self::DefaultConten]))
        {
            return $this->campos[$name][self::DefaultConten];
        }
    }

//  abstract protected function Campos();

    public function __get($name)
    {

        if (isset($this->_ValuesModel[$name]))
        {
            if ($this->_ValuesModel[$name] instanceof ValidDependence && isset($this->_ValuesModel[$name]->option['StrictValue']) && $this->_ValuesModel[$name]->option['StrictValue'] == true)
            {
                return $this->_ValuesModel[$name]->get();
            } else
            {
                return $this->_ValuesModel[$name];
            }
        } else
        {
            ErrorHandle::Notice("Propiedad '" . $name . "' no definida ");
        }
    }

    public function offsetGet($offset)
    {
        if (isset($this->_ValuesModel[$offset]))
        {
            if ($this->_ValuesModel[$offset] instanceof ValidDependence && isset($this->_ValuesModel[$offset]->option['StrictValue']) && $this->_ValuesModel[$offset]->option['StrictValue'] == true)
            {
                return $this->_ValuesModel[$offset]->get();
            } else
            {
                return $this->_ValuesModel[$offset];
            }
        } else
        {
            ErrorHandle::Notice("Indice '" . $offset . "' no definido ");
        }
    }

    /**
     * indica si se recibieron los datos del modelo
     * @return bool
     */
    public function IsSubmited()
    {
        if ($this->inyected)
        {
            $this->Request();
        }
        return $this->Sumited;
    }

    /**
     * indica si los datos del modelo son validos
     * @return bool
     */
    public function IsValid()
    {
        return $this->valid;
    }

    /**
     * 
     * @return boolean
     */
    private function ProcessSubmit()
    {

        if ($this->Method == 'GET')
        {
            if (isset($_GET[$this->NameSubmited]))
            {
                $this->Sumited = true;
                $r = $this->ValidateValues($_GET);
            } else
            {

                return false;
            }
        } elseif ($this->Method == 'POST')
        {
            if (isset($_POST[$this->NameSubmited]))
            {
                $this->Sumited = true;
                $r = $this->ValidateValues($_POST);
            } else
            {
                return false;
            }
        } else
        {
            $r = false;
        }

        if ($this->existFile)
        {
            $this->ValidateFile();
        }


        if ($this->protected)
        {

            if (!Mvc::App()->Router->is_self(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''))
            {
                $r = false;
            }
        }
        if ($this->Sumited)
        {
            if (method_exists($this, 'OnSubmit'))
            {
                $this->OnSubmit(...Mvc::App()->DependenceInyector->SetFunction([$this, 'OnSubmit'])->Param());
            }
        }
        return $r;
    }

    private function ValidateFile()
    {

        foreach ($this->existFile as $v)
        {
            $this->_ValuesModel[$v] = new PostFiles($v);

            $options = $this->campos[$v][self::Validate][1][0]['opt_files'];
            if (key_exists('required', $options) && $options['required'])
            {
                if (!$this->_ValuesModel[$v]->is_Uploaded())
                {
                    $this->valid = false;
                    return false;
                }
            }
            if ($this->_ValuesModel[$v]->is_Uploaded() && isset($options['ext']))
            {
                if (!in_array($this->_ValuesModel[$v]->getExtension(), explode(',', $options['ext'])))
                {
                    $this->valid = false;
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * validacion 
     * @param array $values
     * @return boolean
     */
    protected function ValidateValues(array $values)
    {
        $types = [];
        $val = [];
        $valid = true;
        foreach ($this->campos as $i => $v)
        {
            $type = $v[self::TypeHtml];


            if (!isset($values[$i]))
            {
                $val[$i] = NULL;
            } else
            {
                $val[$i] = $values[$i];
            }
            if ($type != 'file')
                $types[$i] = $v[self::Validate];
        }

        $valuesModel = ValidDefault::Filter($val, $types, ValidDefault::DefaultValid);
        if (!$valid || !$valuesModel->IsValid())
        {

            $this->valid = false;
            $this->_ValuesModel = &$valuesModel;
            return false;
        } else
        {

            $this->_ValuesModel = &$valuesModel;
            $this->valid = true;
            return true;
        }
    }

    /**
     * imprime un imput desde el nombre de un dato en el modelo
     * @param string $name
     * @param array $attrs [attr=>valor]
     * @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function Input($name, $attrs = [], $return = false)
    {

        if (is_object($attrs) && $attrs instanceof \Smarty_Internal_Template)
        {

            $attrs = $name;
            $name = $attrs['name'];
            $return = false;
        }
        if (isset($this->campos[$name]))
        {
            if (preg_match('/\w\[\]/', $this->campos[$name][self::TypeHtml]))
            {
                $attrs['type'] = str_replace('[]', '', $this->campos[$name][self::TypeHtml]);
                $attrs['name'] = $name . '[]';
            } else
            {
                $attrs['type'] = $this->campos[$name][self::TypeHtml];
                $attrs['name'] = $name;
            }


// echo "<input type='" . $this->campos[$name][0] . "' name='" . $name . "'";
            if (isset($this->campos[$name][self::DefaultConten]))
            {
                if (is_array($this->campos[$name][self::DefaultConten]) || $this->campos[$name][self::DefaultConten] instanceof ValidArray)
                {
                    $attrs['value'] = '';
                } else
                {
                    $attrs['value'] = $this->campos[$name][self::DefaultConten];
                }
            }
            $attrValid = [ 'pattern', 'min', 'max', 'maxlength', 'size', 'accept', 'step', 'required', 'multiple', 'title', 'placeholder', 'checked'];
            if ($valid = ValidDefault::GetOptions($this->campos[$name][self::Validate]))
            {

                foreach ($valid as $i => $v)
                {
                    if (in_array($i, $attrValid))
                    {
                        $attrs[$i] = $v;
                    }
                }
            }

            $buff = Html::input($attrs);
            if ($buff)
            {
                return $buff;
            } else
            {
                echo $buff;
            }
        } else
        {
            ErrorHandle::Notice('EL CAMPO ' . $name . ' NO EXISTE');
        }
    }

    /**
     * imprime un textarea desde el nombre de un dato en el modelo
     * @param string $name
     * @param array $attrs [attr=>valor]
     *  @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function TextArea($name, $attrs = [], $return = false)
    {
        if (is_object($attrs) && $attrs instanceof \Smarty_Internal_Template)
        {

            $attrs = $name;
            $name = $attrs['name'];
            $return = false;
        }
        if (isset($this->campos[$name]))
        {
            if (preg_match('/\w\[\]/', $this->campos[$name][self::TypeHtml]))
            {
                $attrs['type'] = str_replace('[]', '', $this->campos[$name][self::TypeHtml]);
                $attrs['name'] = $name . '[]';
            } else
            {
                $attrs['type'] = $this->campos[$name][self::TypeHtml];
                $attrs['name'] = $name;
            }
            $value = '';
            if (isset($this->campos[$name][self::DefaultConten]))
            {
                $value = $this->campos[$name][self::DefaultConten];
            }
            $attrValid = [ 'pattern', 'min', 'max', 'maxlength', 'size', 'accept', 'placeholder', 'required'];
            if (isset($this->campos[$name][self::Validate]) && $valid = ValidDefault::GetOptions($this->campos[$name][self::Validate]))
            {
                foreach ($valid as $i => $v)
                {
                    if (in_array($i, $attrValid))
                    {
                        $attrs[$i] = $v;
                    }
                }
            }
            if ($return)
            {
                return Html::textarea($value, $attrs);
            } else
            {
                echo Html::textarea($value, $attrs);
            }
        } else
        {
            ErrorHandle::Notice('EL CAMPO ' . $name . ' NO EXISTE');
        }
    }

    /**
     * imprime un select desde el nombre de un dato en el modelo
     * @param type $name
     * @param array|\Traversable $options [opcion=>sttr]
     * @param array $attrs [attr=>valor]
     *  @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function Select($name, $options = [], array $attrs = [], $return = false)
    {
        if (is_object($options) && $options instanceof \Smarty_Internal_Template)
        {

            $attrs = $name;
            $name = $attrs['name'];
            $options = [];



            $return = false;
        }
        if (isset($this->campos[$name]))
        {
            if (isset($this->campos[$name][self::DefaultConten]))
            {
                $attrs['value'] = $this->campos[$name][self::DefaultConten];
            }
            if (isset($this->campos[$name][self::Validate]) && $valid = ValidDefault::GetOptions($this->campos[$name][self::Validate]))
            {
                if ($options === [] && isset($valid['options']) && (is_array($valid['options']) || $valid['options'] instanceof \Traversable))
                {
                    $options = $valid['options'];
                }
                // var_dump($valid['options']);
            }


            $attrs['name'] = $name;
            if ($return)
            {
                return Html::select($attrs, $options);
            } else
            {
                echo Html::select($attrs, $options);
            }
        } else
        {
            ErrorHandle::Notice('EL CAMPO ' . $name . ' NO EXISTE');
        }
    }

    /**
     * inicia el formulario
     * @param array $attrs
     *  @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function BeginForm($attrs = [], $return = false)
    {
        if ($this->existFile)
        {
            $attrs['ENCTYPE'] = "multipart/form-data";
        }
        $attrs['action'] = $this->action;
        $attrs['method'] = $this->Method;
        if ($return)
        {
            return Html::OpenTang('form', $attrs);
        } else
        {
            echo Html::OpenTang('form', $attrs);
        }
    }

    /**
     * imprime el boton de enviar el formulario
     * @param string $value
     * @param array $attrs
     *  @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function ButtonSubmit($value = '', $attrs = [], $return = false)
    {
        if (is_object($attrs) && $attrs instanceof \Smarty_Internal_Template)
        {

            $attrs = $value;
            $value = $attrs['value'];
            $return = false;
        }
        $attrs['value'] = 1;
        $attrs['name'] = $this->NameSubmited;
        if ($return)
        {
            $r = Html::input(['type' => 'hidden', 'name' => $attrs['name'], 'value' => 1]);
            $r.= Html::button($value, $attrs);
            return $r;
        } else
        {
            echo Html::input(['type' => 'hidden', 'name' => $attrs['name'], 'value' => 1]);
            echo Html::button($value, $attrs);
        }
    }

    /**
     * finaliza el formulario
     *  @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function EndForm($return = false)
    {
        if ($return)
        {
            return Html::CloseTang('form');
        } else
        {
            echo Html::CloseTang('form');
        }
    }

    /**
     * 
     * @param type $attrs
     * @param array $campos
     * @param type $submit
     *  @param bool $return indica si en contenido sera impreso en el buffer o retornado
     */
    public function PrintForm($attrs, array $campos = [], $submit = [], $return = false)
    {
        $specialTang = ['select', 'textarea', 'hidden'];
        $buff = '';
        $buff.=$this->BeginForm($attrs, true);

        $buff.= Html::OpenTang('ul', ['class' => 'FormList']);
        foreach ($this->campos as $i => $v)
        {
            $typeHtml = $v[self::TypeHtml];
            $name = isset($campos[$i]['text']) ? $campos[$i]['text'] : '';
            $attr = isset($campos[$i]['attr']) ? $campos[$i]['attr'] : [];
            if ($typeHtml == 'hidden')
            {
                $buff.=$this->Input($i, $attr, true);
                continue;
            }
            $buff.= Html::OpenTang('li', ['class' => 'FormRow']) . Html::label($name, ['from' => $i]);
            if (!in_array($typeHtml, $specialTang))
            {
                if (isset($campos[$i]['ListValue']))
                {
                    foreach ($campos[$i]['ListValue'] as $val)
                    {
                        $buff.= $this->Input($i, $attr + ['value' => $val], true);
                    }
                } else
                {
                    $buff.=$this->Input($i, $attr, true);
                }
            } else
            {
                switch ($typeHtml)
                {
                    case 'textarea':
                    case 'textarea[]':
                        $buff.=$this->TextArea($i, $attr, true);
                        break;
                    case 'select':

                        if (isset($campos[$i]) && isset($campos[$i]['option']) && (is_array($campos[$i]['option']) || $campos[$i]['option'] instanceof \Traversable))
                        {

                            $buff.=$this->Select($i, $campos[$i]['option'], $attr, true);
                        } else
                        {

                            $buff.=$this->Select($i, [], $attr, true);
                        }
                        break;
                }
            }
            $buff.= Html::CloseTang('li');
        }

        $buff.= Html::OpenTang('li', ['class' => 'FormRow']);
        $buff.=$this->ButtonSubmit(isset($submit['value']) ? $submit['value'] : 'ENVIAR', $submit, true);
        $buff.= Html::CloseTang('li');
        $buff.= Html::CloseTang('ul');
        $buff.=$this->EndForm(true);
        if ($return)
        {
            return $buff;
        } else
        {
            echo $buff;
        }
    }

    public function Form($params, $content, &$smarty, &$repeat)
    {
        if (!isset($content))
        {
            $content = $this->BeginForm($params, true);
        } else
        {
            $content = $content . $this->EndForm(true);
        }
        return $content;
    }

    public function ParseSmaryTpl()
    {
        $smarty = parent::ParseSmaryTpl();
        $smarty['allowed'] = [];
        $smarty['block_methods'][] = 'Form';
        return $smarty;
    }

}
