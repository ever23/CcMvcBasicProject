<?php

namespace Cc\Mvc;

use Cc\Mvc;
use Cc\DependenceInyector;
use Cc\HelperArray;

/**
 * maneja la autenticacion de usuarios del servidor
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package CcMvc
 * @subpackage Session 
 * @example ../examples/CERQU/protected/model/AutenticaRQU.php EJEMPLO DE UNA CLASE AUTENTICADORA DE CcMvc #1
 * 
 *
 * @method array Autentica() Autentica(mixes ..$_) SERA LLAMADO AUTOMATICAMENTE CUANDO SE REQUIERA AUTENTICA Y DEVE RETORNAR UN ARRAY ASOCIATIVO CON LOS PARAMETROS A SER COMPARADOS
 * @method void OnFailed() OnFailed(mixes ...$_)  ESTE METODO SE EJECUTARA CUANDO LA AUTENTICACION FALLE
 * @method void OnSuccess() OnSuccess(mixes ...$_)  ESTE METODO SE EJECUTARA CUANDO LA AUTENTICACION TENGA EXITO.
 * @uses DependenceInyector SE UTILIZA PARA IYECTAR LOS PARAMETROS EN LOS METODOS Autentica, OnFailed Y OnSuccess
 *
 */
abstract class Autenticate extends Model
{

    private $exept = [];
    protected static $param = [];

    /**
     *
     * @var Config 
     */
    protected $conf;
    protected static $ReadAndClose = false;

    /**
     *
     * @var DependenceInyector
     */
    protected $DependenceInyector;
    private $statusClose = false;
    protected $AccessUser = [];
    private $falied = NULL;
    protected $InternalSession;

    /**
     * INDICA QUE LA AUTENTICACION FALLO PUEDE SER COMPARADA CON 
     * EL RETORNO DE METODO {@link Autenticate::TypeFailed}
     * 
     */
    const FailedAuth = 1;
    const NoAuth = 2;

    /**
     *  CONSTRUCTOR DE LA CLASE
     *  @param array $exet UN ARRAY CON EL NOMBRE DE LOS CONTROLADORES DONDE NO SE ARA UN EXEPCION Y NO SE EJECUTARA LA AUTENTICACION
     *  GENERALMENTE ESTOS NOMBRES DEVEN SER DEFINIDOS EN EL DOCUMENTO DE CONFIGURACION DE LA APP EN LOS PARAMENTROS  DE ATENTICACION
     */
    public function __construct(array $exet = [], array $param = [])
    {

        parent::__construct();
        $conf = Mvc::App()->Config();

        $this->EstableceParam($param);
        $this->exept = $exet;
        self::$ReadAndClose = (isset($conf['Autenticate']['SessionCookie']['ReadAndClose']) ? $conf['Autenticate']['SessionCookie']['ReadAndClose'] : false) && $this->is_Autenticable();
    }

    public function __destruct()
    {
        $this->InternalSession[static::class] = $this->_ValuesModel;
    }

    protected function Campos()
    {
        return [];
    }

    public function Destroy()
    {
        $this->InternalSession[static::class] = [];
    }

    public function Del()
    {
        $this->_ValuesModel = ['initialized' => true];

        // $this->TingerEvent('OnSessionRegister');
    }

    public function Start(InternalSession &$session)
    {
        $this->InternalSession = &$session;

        if (isset($session[static::class]) && isset($session[static::class]['initialized']))
        {
            $this->_ValuesModel = &$session->GetRefenece(static::class);
        } else
        {

            $session[static::class] = ['initialized' => true];

            $this->_ValuesModel = &$session->GetRefenece(static::class);
            if (!is_array($this->_ValuesModel))
            {
                $session[static::class] = ['initialized' => true];
            }

            $this->TingerEvent('OnSessionRegister');
        }
    }

    /**
     * LANZA UN FALLO DE AUTENTIFICACION
     * @param int $type razon del fallo
     * @param string $mensaje mensaje 
     */
    protected function TingerFailed($type, $mensaje)
    {
        //var_dump($this->falied);
        if (is_null($this->falied))
        {
            $this->falied = $type;
            $result = $this->TingerEvent('OnFailed');
            if ($result === false && $type !== self::NoAuth)
            {
                Mvc::App()->LoadError(401, $mensaje . ",<BR> PARA EVITAR ESTE MENSAJE DEBERIA DEFINIR EL METODO OnFailed EN LA CLASE " . static::class);
                exit;
            }
        }
    }

    protected function TingerEvent($Event)
    {

        if (method_exists($this, $Event))
        {
            $ret = $this->$Event(... $this->DependenceInyector->SetFunction([$this, $Event])->Param());
            if ($ret !== NULL)
            {
                return $ret;
            }
            return true;
        }
        return NULL;
    }

    /**
     * INDICA LA RAZON DEL FALLO EN LA AUTENTIFICACION 
     * @return int
     * @deprecate
     */
    protected function TypeFailed()
    {
        return $this->falied;
    }

    /**
     * INDICA SI LA AUTENTICACION FALLO SI ES ASI RETORNA EL NUMERO QUE IDENTIFICA EL FALLO 
     * DE LO CONTRARIO RETORNA FALSE
     * @return int
     * 
     */
    public function IsFailed()
    {
        return $this->falied;
    }

    /**
     * 
     * @param boolean $read
     */
    public function SetReadAndClose($read = false)
    {
        self::$ReadAndClose = $read;
    }

    /**
     * @access private
     * @param type $n
     * @param type $v
     */
    public function __set($n, $v)
    {
        if ($this->statusClose)
        {
            ErrorHandle::Notice("La modificacion del valor de \$_SESSION[$n] no surtira efecto ya que se cerro el archivo de session");
        }
        parent::__set($n, $v);
    }

    /**
     * @access private
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {

        if ($this->statusClose)
        {
            ErrorHandle::Notice("La modificacion del valor de \$_SESSION[$offset] no surtira efecto ya que se cerro el archivo de session");
        }
        parent::offsetSet($offset, $value);
    }

    /**
     * @access private
     * @param type $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->statusClose)
        {
            ErrorHandle::Notice("La modificacion del valor de \$_SESSION[$offset] no surtira efecto ya que se cerro el archivo de session");
        }
        parent::offsetUnset($offset);
    }

    /**
     *  ESTABLECE LOS PARAMETROS QUE SE USARAN PARA AUTENTICAR
     *  @param array $param ARRAY CON LOS NOMBRE DE LOS PARAMETROS QUE SE QUE SE BUSCARAN EN EL ARRAY DE SESSION
     */
    protected final function EstableceParam($param)
    {
        self::$param = $param;
    }

    /**
     *  VERIFICA QUE TODOS LOS PARAMETROS EXISTAN EN EL ARRAY DE SESSION
     *  @return bool true si existen todos false si falta uno
     */
    public static final function ExisParam()
    {
        $param = self::$param;
        foreach ($param as $p)
        {
            if (self::_empty($p))
            {
                return false;
            }
        }
        return true;
    }

    /**
     *  INGRESA LOS VALORES DE LOS PARAMETROS EN EL ARRAY DE SESSION
     *  @param array $params UN ARRAY ASOCIATIVO CON LOS MPARAMETROS COMO INDICE Y VALORES DE LOS MISMOS EN VALOR
     *  @return bool true si tubo exito false si no
     */
    public final function SetParam(array $params)
    {
        foreach (self::$param as $p)
        {
            if (isset($params[$p]))
            {
                self::SetVar($p, $params[$p]);
            } else
            {
                return false;
            }
        }
        return true;
    }

    /**
     * indica si en el controlador se ejecurara la autenticacion
     * @return boolean
     */
    protected function is_Autenticable()
    {
        $refection = Mvc::App()->SelectorController->GetReflectionController();
        if ($refection->implementsInterface(AccessUserController::class))
        {

            $class = $refection->name;
            $this->AccessUser = $class::AccessUser();
            $controller = Mvc::App()->GetController();

            $this->AccessUser = HelperArray::TolowerRecursive($this->AccessUser);
            if ($refection->implementsInterface(ReRouterMethod::class))
            {
                if (!$refection->hasMethod($controller['method']))
                {
                    if (isset($this->AccessUser['NoAth']) && in_array('__routermethod', $this->AccessUser['NoAth']))
                    {
                        unset($this->AccessUser['NoAth']);
                        return false;
                    }
                }

                foreach ($this->AccessUser as $i => $v)
                {
                    foreach ($v as $ii => $vv)
                    {
                        if (is_array($vv))
                            foreach (array_keys($vv, '__routermethod') as $i2 => $v2)
                            {
                                $this->AccessUser[$i][$ii][$v2] = strtolower($controller['method']);
                            }
                    }
                }
            }

            if (isset($controller['orig']) && !Controllers::GetReflectionClass()->hasMethod($controller['method']))
            {
                if (!$refection->hasMethod($controller['method']))
                {
                    if (isset($this->AccessUser['NoAth']) && in_array($controller['orig'], $this->AccessUser['NoAth']))
                    {
                        unset($this->AccessUser['NoAth']);
                        return false;
                    }
                }

                foreach ($this->AccessUser as $i => $v)
                {
                    foreach ($v as $ii => $vv)
                    {
                        if (is_array($vv))
                            foreach (array_keys($vv, $controller['orig']) as $i2 => $v2)
                            {
                                $this->AccessUser[$i][$ii][$v2] = strtolower($controller['method']);
                            }
                    }
                }
            }
            if (isset($this->AccessUser['NoAth']) && in_array(strtolower($controller['method']), $this->AccessUser['NoAth']))
            {
                unset($this->AccessUser['NoAth']);

                return false;
            }
            unset($this->AccessUser['NoAth']);
            return true;

            // $this->exept+=$access[''];
        }


        foreach ($this->exept as $v)
        {

            //if($cont['class']==$v || (is_array($v) && ($cont['paquete']==(isset($v['paquete'])?$v['paquete']:NULL) && $cont['class']==$v['class'] && $cont['method']==$v['method'])) )
            if (Mvc::App()->Router->is_self($v))
            {

                return false;
            }
        }
        return true;
    }

    /**
     * ESTABLECE EL OBJETO INYECTOR DE DEPENDENCIAS 
     * @param DependenceInyector $d
     */
    public function SetDependenceInyector(DependenceInyector &$d)
    {
        $this->DependenceInyector = &$d;
    }

    /**
     *  EJECUTA TODA LA AUTENTICACION NO PUEDE SER REDEFINIDO
     *  @return bool true si autentico con exito false se fallo
     * @internal 
     */
    public function Auth()
    {
        if (!$this->is_Autenticable())
        {
            $this->TingerFailed(self::NoAuth, 'No se autentico');


            return FALSE;
        } elseif (!$this->verifica())
        {
            $this->TingerFailed(self::FailedAuth, 'AUTENTIFICACION FALLIDA');

            return FALSE;
        } else if ($this->TingerEvent('OnSuccess') === false)
        {
            $this->TingerFailed(self::FailedAuth, 'AUTENTIFICACION FALLIDA');
        }


        if (self::$ReadAndClose)
        {
            $this->Commit();
        }
        return true;
    }

    /**
     * VERIFICA LOS VALORES RETORNADOS POR {@link Autentica} CON LOS VALORES DE LA SESSION
     * @return boolean
     */
    protected function verifica()
    {

        if (!method_exists($this, 'Autentica'))
        {
            throw new Exception("Debes definir el metodo " . static::class . "::Autentica");
        }
        $this->DependenceInyector->SetFunction([$this, 'Autentica']);
        $athu = $this->Autentica(... $this->DependenceInyector->Param());


        $retur = true;

        foreach (self::$param as $p)
        {
            if (!isset($athu[$p]) || $athu[$p] != $this->offsetGet($p))
            {
                $retur = false;
            } else
            {
                unset($athu[$p]);
            }
        }

        foreach ($athu as $i => $v)
        {
            $this->offsetSet($i, $v);
        }


        return $retur;
    }

    public function Commit()
    {
        $this->statusClose = true;
    }

}
