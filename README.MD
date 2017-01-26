# EJEMPLO DE PROYECTO CcMvc

Este es un proyecto base para desmostrar el funcionamiento de  [CcMvc Framework](https://github.com/ever23/CcMvc "CcMvc") 
explica las caracteristicas mas resaltantes

se puede tomar como base para comenzar un proyecto nuevo bajo este framework

En el codigo fuente se encuentra explicacion detallada del funcionamiento puedes verlo en los archivos .php que se encuentran 
en directorio [/protected](protected/ "protected") y el archivo [index.php](public_html/index.php "index.php")

En este proyecto se encuentran ejemplos de view y layauts en difrenetes formatos 
esto para demostrar que CcMvc puede usar facilmente Motores de Plantillas como Smarty y Twig incluso plantillas 
de php nativo usando la misma inteface 

El proyecto tiene una base de datos en sqlite pero es perfectamente funcional en mysql y posgres 

##En este proyecto se explica:

Enrutamiento automatico y manual de controladores 

Minifi automatico de javascript, styles y html

Uso de DomPdf para crea pdf automaticamente 

Redimencionamiento de imagenes desde el servidor

Uso basico de Formularios con  Cc\Mvc\FormModel

Uso basico de Bases de datos con Cc\Mvc\DBtabla]
    
Uso basico de Inyeccion de Dependencias 

## Como Instalar 

Solo tienes que descargar el proyecto o hacer un clone desde github y colocarlo en un directorio de tu servidor local luego instalara los paquetes con composer y ejecutar con tu navegador 
preferido 

## Crear un nuevo proyecto CcMvc 
crea el directorio donde estara tu proyecto luego crea el archivo composer.json en el mismo 
```json
{
	"require":
	{
		"ccmvc/project":"0.8.5.5-dev"
	}
}
```
luego ejecuta
```sh
composer install ccmvc/project 
```
Luego posicionate en vendor/bin/ y ejecuta 
```sh
CcMvc Install 
```
y listo tienes tu nuevo proyecto CcMvc 

### TE INVITO A PROBAR EL FRAMEWORK Y SI ES POSIBLE COLABORAR CON EL DESARROLLO DEL MISMO Y ASI HACER CRECER ESTE PROYECTO 
Puedes enviarme sugenerncias y recomendaciones a enyerverfranco@gmail.com 