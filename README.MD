# EJEMPLO DE PROYECTO CcMvc

Este es un proyecto base para desmostrar el funcionamiento de  [CcMvc Framework](https://github.com/ever23/CcMvc "CcMvc") 
explica las caracteristicas mas resaltantes

se puede tomar como base para comenzar un proyecto nuevo bajo este framework

En el codigo fuente se encuentra explicacion detallada del funcionamiento puedes verlo en los archivos .php que se encuentran 
en directorio [/protected](https://github.com/ever23/CcMvcBasicProject/protected "protected") y el archivo [index.php](https://github.com/ever23/CcMvcBasicProject/index.php "index.php")

En este proyecto se encuentran ejemplos de view y layauts en difrenetes formatos 
esto para demostrar que CcMvc puede usar facilmente Motores de Plantillas como Smarty y Twig incluso plantillas 
de php nativo usando la misma inteface, se pueden agregar facilmente otros motrores de plantillas usando la interface [Cc\Mvc\ViewLoaderExt](https://github.com/ever23/CcMvc/src/mvc/view/ViewLoader.php "Cc\Mvc\ViewLoaderExt")
y resgitrando la clase su archivo de configuracion  del proyecto, para ver ejemplo de esto vea el archivo de 
[configuracion por defecto](https://github.com/ever23/CcMvc/blob/master/src/mvc/config/DefaultConfig.php "CcMvc") de CcMvc en el indice ['ViewLoaders']

El proyecto tiene una base de datos en sqlite pero perfectamente se puede cambiar a 
MySql solo modificando en el archivo de  [configuracion](https://github.com/ever23/CcMvcProject/blob/master/blob/master/protected/configuracion.php "configuracion") en 
el indice DB colocando en el include en archivo [Mysql.php](https://github.com/ever23/CcMvcProject/protected/Mysql.php "Mysql") y cargando 
el archivo [testdb.sql](https://github.com/ever23/CcMvcProject/blob/master/protected/Mysql.php "Mysql") en su servidor mysql 

En este proyecto se explica:

Enritamiento automatico y manual de controladores 

Minifi automatico de javascript, styles y html

Uso de DomPdf para crea pdf automaticamente 

Redimencionamiento de imagenes desde el servidor

Uso basico de Formularios con  [Cc\Mvc\FormModel](https://github.com/ever23/CcMvc/blob/master/src/mvc/model/FormModel/FormModel.php "CcMvc") 

Uso basico de Bases de datos con [Cc\Mvc\DBtabla](https://github.com/ever23/CcMvc/blob/master/src/Mvc/DataBase/DataBase.php "CcMvc")
    
Uso basico de Inyeccion de Dependencias 

mas adelante se segiran incorporando mas ejemplos del funcionamiento de este famework al proyecto como los siguientes:

Sessiones y Autenticacion de usuarios con [Cc\Mvc\AutenticateUserDB](https://github.com/ever23/CcMvc/blob/master/src/mvc/session/AutenticateUserDB.php "CcMvc")
   
Uso completo de Bases De datos con [Cc\DBtabla](https://github.com/ever23/CcMvc/blob/master/src/Cc/DataBase/DBtabla.php "CcMvc") y [Cc\Mvc\DBtabla](https://github.com/ever23/CcMvc/blob/master/src/Mvc/DataBase/DataBase.php "CcMvc") (CcMvc Actualmente soporta SQLite, MyQli y PosgreSQL)
    
Crear directorios dinamicos 
    
Recibir Archivos Desde un formulario con [Cc\Mvc\PostFiles](https://github.com/ever23/CcMvc/blob/master/src/mvc/request/PostFiles.php "CcMvc") y, o [Cc\Mvc\FormModel](https://github.com/ever23/CcMvc/blob/master/src/mvc/model/FormModel/FormModel.php "CcMvc") 
    
Ajax con CcMvc 
    
 Almacenar cache de imagenes dinamicas 
    
Enviar Html via Email usando Views y plantillas 
    
Como Protegerse de ataque Xss y Sqli con CcMvc 

Crear dependencias Inyectables 
    
Extender CcMvc 

Entre otras caracteristicas que aun falta mostrar en este proyecto base... 


## Como Instalar 

Solo tienes que descargar el proyecto o hacer un clone desde github y colocarlo en un directorio de tu servidor local luego ejecutar con tu navegador 
preferido 

### TE INVITO A PROBAR EL FRAMEWORK Y SI ES POSIBLE COLABORRAR CON EL DESARROLLO DEL MISMO Y ASI HACER CRECER ESTE PROYECTO 
Puedes enviarme sugenerncias y recomendaciones a enyerverfranco@gmail.com 