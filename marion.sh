#!/bin/bash
dirname="${PWD##*/}"

# store arguments in a special array 
args=("$@") 
# get number of elements 
numargs=${#args[@]} 
green=`tput setaf 2`
red=`tput setaf 1`

envup() {
  local file=$([ -z "$1" ] && echo ".env" || echo ".env.$1")

  if [ -f $file ]; then
    set -a
    source $file
    set +a
  else
    echo "No $file file found" 1>&2
    return 1
  fi
}

get_type_ctrl () {
   type=''
   for a in "${args[@]}"
   do
		
		if [[ "$a" == *"--type="* ]];
		then
			type=$(echo "$a" | sed -e 's/--type=//')
			
		fi
		
	done
	
	echo $type;
}

get_mod_name () {
   mod=''
   for a in "${args[@]}"
   do
		
		if [[ "$a" == *"--module="* ]];
		then
			mod=$(echo "$a" | sed -e 's/--module=//')
			
		fi
		
	done
	
	echo $mod;
}

get_page_name () {
   page=''
   for a in "${args[@]}"
   do
		
		if [[ "$a" == *"--page="* ]];
		then
			page=$(echo "$a" | sed -e 's/--page=//')
			
		fi
		
	done
	
	echo $page;
}



create_module(){
	if [ ! -d "modules" ]
	then 
		echo $red"Errore: puoi utilizzare lo script solo nella root del progetto"
		exit;
	fi
	if [[ "$tag" == "" ]]; then 
		read -p "Identificativo: " tag
		
		while [ -d "modules/$tag" ]
		do
			echo "Errore: questo identificativo è già utilizzato da un altro modulo"
			read -p "Inserisci un nuovo identificativo: " tag
		done
	else
		
		if [ -d "modules/$tag" ]; then 
			echo "Errore: questo identificativo è già utilizzato da un altro modulo"
			exit;
		fi
	fi
	read -p "Nome del modulo: " name
	while [[ "$name" == "" ]]
	do
		echo "Errore: devi specificare il nome del modulo"
		read -p "Nome del modulo: " name
	done
	read -p "Autore del modulo: " author
	read -p "Descrizione del modulo: " description
	read -p "Tipologia [cms/catalog/ecommerce/payment/other]: " kind
	while [ $kind != 'cms' ] && [ $kind != 'catalog' ] && [ $kind != 'ecommerce' ] && [ $kind != 'payment' ] && [ $kind != 'other' ]
	do
		read -p "Tipologia [cms/catalog/ecommerce/payment/other]: " kind
	done
	read -p "Prevede un autoload? [s/n]: " has_autoload
	while [ $has_autoload != 's' ] && [ $has_autoload != 'n' ]
	do
		read -p "Prevede un autoload? [s/n]: " has_autoload
	done
	flag_autoload=0
	if [ $has_autoload == 's' ]
	then
		flag_autoload=1
	fi


	read -p "Ha un widget? [s/n]: " has_widget
	while [ $has_widget != 's' ] && [ $has_widget != 'n' ]
	do
		read -p "Ha un widget? [s/n]: " has_widget
	done
	if [ $has_widget == 's' ]
	then 
		read -p "Nome widget: " name_widget
		while [[ "$name_widget" == "" ]]
		do
			echo "Errore: devi specificare il nome del widget"
			read -p "Nome widget: " name_widget
		done
		read -p "Classe widget [CamelCase]: " class_widget
		while [[ "$class_widget" == "" ]]
		do
			echo "Errore: devi specificare la classe del widget"
			read -p "Classe widget [CamelCase]: " class_widget
		done
	fi



	mkdir "modules/$tag"
	cd "modules/$tag"
	mkdir 'controllers'
	mkdir 'migrations'
	mkdir 'controllers/admin'
	mkdir 'controllers/front'
	mkdir 'templates_twig'
	mkdir 'templates_twig/admin'
	mkdir 'templates_twig/front'
	mkdir 'templates_twig/widgets'
	mkdir 'img'
	if [ $has_autoload == 's' ]
	then 
		mkdir 'src'
	fi
	mkdir 'translate'
	echo '<?php
//file contenente le traduzioni in it
'   > translate/it.php
	echo '<?php
//file contenente le traduzioni in en
'   > translate/en.php
	if [ $has_widget == 's' ]
	then
	mkdir 'templates_twig/widgets'
	echo "{% extends 'layouts/base_form_widget.htm' %}
{% block content %}
{% import 'macro/form.htm' as form %}

<!---



	CONTENTUTO FORM IMPOSTAZIONI

	{# This will be a

		{{form.buildCol(dataform.namefield)}}

		{{form.buildCol(dataform.nome,'col-md-12')}}
		{{form.buildCol(dataform.cognome,'col-md-6')}}
	#}
	
-->



{% endblock %}" > 'templates_twig/admin/widget_setting.htm'
				echo '<?php
use Marion\Core\Marion;
use Marion\Controllers\ModuleController;
class WidgetController extends ModuleController{
	public $_auth = "cms";
	private $_form_control = "name_form_control"; // da creare opportunamente

	

	function display(){
		$database = Marion::getDB();
		$this->id_box = _var("id_box");
		$this->setVar("id_box",_var("id_box"));
		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			$array = $this->checkDataForm($this->_form_control,$formdata);
			if( $array[0] == "ok"){
				unset($array[0]);
				
				$data = array();
				foreach($array as $k => $v){
					if( $k != "_locale_data"){
						$data[$k] = $v;
					}
				}
				foreach($array["_locale_data"] as $k =>$v){
					foreach($v as $k1 => $v1){
						$data[$k1][$k] = $v1;
					}
				}
		
				
				$dati = serialize($data);
				
				$database->update("composition_page_tmp","id={$this->id_box}",array("parameters"=>$dati));
				
				$this->displayMessage("Dati salati con successo!","success");
			}else{
				$this->errors[]= $array[1];
			}
			$dati = $formdata;
			
		}else{
			$data = $database->select("*","composition_page_tmp","id={$this->id_box}");
			
			if( okArray($data) ){
				$dati = unserialize($data[0]["parameters"]);
			}
			
		}

		$dataform = $this->getDataForm($this->_form_control,$dati);
		
		$this->setVar("dataform",$dataform);

		$this->output("widget_setting.htm");
	}


	
	
	


	// FUNZIONI PER IL FORM
	function my_custom_function(){
		$toreturn = array();

		return $toreturn;
	}


}



?>' > 'controllers/admin/WidgetController.php'

				echo '<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class '$class_widget'Component extends  PageComposerComponent{
	
	public $template_html = "render.htm"; //html del widget
	

	function registerJS($data=null){
		/*
			se il widget necessita di un file js allora occorre registralo in questo modo
			
			PageComposer::registerJS("url del file"); // viene caricato alla fine della pagina
			PageComposer::registerJS("url del file","head"); // viene caricato nel head 
			

		*/
		PageComposer::registerJS(_MARION_BASE_URL_."modules/'$tag'/js/script.js");
	}
	function registerCSS($data=null){
		/*
			se il widget necessita di un file css allora occorre registralo in questo modo
			
			PageComposer::registerCSS("url del file"); 
			

		*/
		PageComposer::registerCSS(_MARION_BASE_URL_."modules/'$tag'/css/style.css");
	}

	function build($data=null){
			
			//$this->getTemplateTwig(basename(__DIR__)); //oggetto di tipo template che legge nei template del modulo
	
			
			
			/*$parameters: parametri di configurazione del widget
			Questo array contiene i parametri di configurazione del widget
			*/
			$parameters = $this->getParameters();


			/*
				INSERISCI IL CODICE DEL WIDGET




			*/

			//imposto una variabile nella pagina da mostrare
			$this->setVar("nome_variabile","valore_variabile");

			
			$this->output($this->template_html);
				
		
	}

}
' > 'widget.php'

				echo '<b>'$tag'</b> works!' > 'templates_twig/widgets/render.htm'
				fi
				mkdir 'css'
				mkdir 'js'
				touch js/script.js
				touch css/style.css
				if [ $has_widget == 's' ]
				then
				echo '<?xml version="1.0"?>
<module>
	<info> 
		<author>'$author'</author>
		<name>'$name'</name>
		<permission>superadmin</permission>
		<tag>'$tag'</tag>
		<kind>'$kind'</kind>
		<scope></scope> 
		<autoload>'$flag_autoload'</autoload> 
		<description><![CDATA['$description']]></description> 
	</info>
	<widgets>
	<widget> 
			<name>'$name_widget'</name>
			<function>'$class_widget'Component</function>
			<url_conf>index.php?ctrl=Widget&amp;mod='$tag'</url_conf>
			<repeatable>1</repeatable>
		</widget>
	</widgets>
</module>' > "config.xml"
			else
			echo "<?xml version='1.0'?>
<module>
	<info> 
		<author>$author</author>
		<name>$name</name>
		<permission>superadmin</permission>
		<tag>$tag</tag>
		<kind>$kind</kind>
		<scope></scope> 
		<autoload>$flag_autoload</autoload> 
		<description><![CDATA[$description]]></description> 
	</info>
</module>" > "config.xml"
			fi
			name_class=''
			for a in ${tag//_/ }
			do
				b=`echo $a | awk '{print toupper(substr($0,0,1))tolower(substr($0,2))}'`
				name_class="${name_class}${b}"
			done
			echo '
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
class '$name_class' extends Marion\Core\Module{
	/*
		OVERRIDE INSTALL
	*/
	function install(){
		$res = parent::install();
		if( $res ){
			/*
			//per creare una tabella
			DB::schema()->create("table",function(Blueprint $table){
				$table->id(); //crea un campo id (autoincremnet,unsigned,bigint(20))
				$table->string("field");
			});

			*/
		}


		return $res;
	}


	/*
		OVERRIDE UNINSTALL
	*/
	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			/*
			//per cancellare una tabella
			DB::schema()->dropIfExists("table");
			*/

		}	
		return $res;
	}

	/*
		OVERRIDE ACTIVE
	*/
	function active()
	{	
		
		parent::active();
	}

	/*
		OVERRIDE DISABLE
	*/
	function disable()
	{
		
		parent::disable();
		
	}

	/**
		OVERRIDE SEEDER
	**/
	function seeder(){
		$faker = $this->getFaker();
	}


}
?>' > $tag".php"
	echo '<?php
global $_routes;
/*
$_routes["'$tag'/myroute"] = [
    "controller" => "controller_name",
    "module" => "'$tag'",
    "method" => "method_name"
];
*/
?>' > routes.php
		if [ $has_autoload == 's' ]
		then 
			echo '
{
    "autoload": {
        "psr-4": {"'$name_class'\\": "src/"}
    }
}

				'> 'composer.json'
				fi
				cd ../../ 
				echo $green"modules/"$tag

}

create_model(){
	if [ ! -d "modules/$module" ]
	then 
		echo $red"Errore: il modulo non esiste"
		exit;
	fi

	if [ $model != 'base' ] && [ $model != 'eloquent' ]; then
		echo $red"Errore: tipo di model non valido (base|eloquent)"
		exit
	fi

	namespace=''
	for a in ${module//_/ }
	do
		b=`echo $a | awk '{print toupper(substr($0,0,1))tolower(substr($0,2))}'`
		namespace="${name_class}${b}"
	done
	if [ -f modules/$module/src/$nameModel".php" ]; then
		echo $red'Errore: il model '$nameModel' già esiste'
		exit;
	fi
	nameClass=`echo "$nameModel" | awk '{print tolower($0)}'`
	case $model in
		'base')

			echo '<?php
namespace '$namespace';
use Marion\Core\Base;
class '$nameModel' extends Base{	
	// COSTANTI DI BASE
	const TABLE = "'$nameClass'"; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = "id"; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ""; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = "";// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ""; //nome del campo padre
	const LOCALE_FIELD_TABLE = ""; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = "it"; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ""; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all amministratore
	const NOTIFY_ADMIN_EMAIL = ""; // email a cui inviare la notifica
}
?>' > modules/$module/src/$nameModel".php"
			echo $green'modules/'$module'/src/'$nameModel'.php'
			docker exec -w /app/modules/$module $dirname"_web_1" composer dump-autoload -o
			;;

		'eloquent')

			echo '<?php
namespace '$namespace';
use \Illuminate\Database\Eloquent\Model;
use Marion\Traits\EloquentModelLang;
use Illuminate\Database\Eloquent\SoftDeletes;
class '$nameModel' extends Model{	
	//use EloquentModelLang; //se si vuole gestire il multilingua
	//use SoftDeletes;
	public $timestamps = false;
	protected $table = "'$module'_'$nameClass'";
	protected $guarded = []; //imposta tutti i campi fillable


	/** CAMPI PER IL MULTILINGUA **/
	//protected $table_lang = "'$module'_'$nameClass'_lang";
	//protected $table_lang_external_key = "'$module'_'$nameClass'_id";
	//protected $table_lang_field = "lang";
}
?>' > modules/$module/src/$nameModel".php"
			echo $green'modules/'$module'/src/'$nameModel'.php'
			docker exec -w /app/modules/$module $dirname"_web_1" composer dump-autoload -o
			;;
		
	esac

}


create_page(){
	if [ ! -d "modules/$module" ]
	then 
		echo $red"Errore: il modulo non esiste"
		exit;
	fi
	if [ $page != 'form' ] && [ $page != 'base' ] && [ $page != 'page' ] && [ $page != 'backend' ] && [ $page != 'widget' ]; then
		echo $red"Errore: occorre specificare il tipo di pagina (--type=page|backend|widget|base|form)"
		exit
	fi
	if [ $page == 'widget' ]; then
		if [ ! -d "modules/$module/templates_twig/widgets" ]
		then 
			mkdir modules/$module/templates_twig/widgets
			echo $green'modules/'$module'/templates_twig/widgets'
		fi
		if [ -f modules/$module/templates_twig/widgets/$name".htm" ]; then
			echo $red'Errore: la pagina '$name'.htm giè esiste'
		else
			touch modules/$module/templates_twig/widgets/$name".htm"
			echo $green'modules/'$module'/templates_twig/widgets/'$name'.htm'
		fi
	fi

	if [ $page == 'page' ] || [ $page == 'backend' ]; then
		if [ ! -d "modules/$module/templates_twig/front" ]
		then 
			mkdir modules/$module/templates_twig/front
			echo $green'modules/'$module'/templates_twig/front'
		fi
		if [ -f modules/$module/templates_twig/front/$name".htm" ]; then
				echo $red'Errore: la pagina '$name'.htm giè esiste'
				else
					if [ $page == 'page' ]; then
					echo '
{% extends "layouts/page.htm" %}
{% block content %}

{% endblock %}' 	> modules/$module/templates_twig/front/$name".htm"
					else
					echo '
{% extends "layouts/page_backend.htm" %}
{% block backend_header %}
	title
{% endblock %}
{% block backend_content %}

{% endblock %}' 	> modules/$module/templates_twig/front/$name".htm"
					fi
					echo $green'modules/'$module'/templates_twig/front/'$name'.htm'
				fi
	fi
	if [ $page == 'form' ] || [ $page == 'base' ]; then
		if [ ! -d "modules/$module/templates_twig/admin" ]
		then 
			mkdir modules/$module/templates_twig/admin
			echo $green'modules/'$module'/templates_twig/admin'
		fi
		if [ -f modules/$module/templates_twig/admin/$name".htm" ]; then
				echo $red'Errore: la pagina '$name'.htm giè esiste'
				else
					if [ $page == 'base' ]; then
					echo '
{% extends "layouts/base.htm" %}
{% block page_title %} Page title {% endblock %} 
{% block content %}

{% endblock %}' 	> modules/$module/templates_twig/admin/$name".htm"
					else
					echo '
{% extends "layouts/base_form.htm" %}
{% block edit_page_title %} Page title {% endblock %} 
{% block content %}
{% import "macro/form.htm" as form %}
{#
 <!-- Per creare un elemento del form  -->
 {{form.build(dataform.field)}}

 <!-- Per creare un elemento del form con div  -->
 {{form.buildCol(dataform.field,'col-md-12')}}
#}
{% endblock %}' 	> modules/$module/templates_twig/admin/$name".htm"
					fi
					echo $green'modules/'$module'/templates_twig/admin/'$name'.htm'
				fi
	fi

	
}

create_ctrl(){
	if [ ! -d "modules/$module" ]
	then 
		echo $red"Errore: il modulo non esiste"
		exit;
	fi

	if [ $ctrl != 'admin' ] && [ $ctrl != 'adminList' ] && [ $ctrl != 'frontend' ] && [ $ctrl != 'backend' ] && [ $ctrl != 'api' ]; then
		echo $red"Errore: tipo di controller non valido (admin|adminList|frontend|backend|api)"
		exit
	fi
	page_name='template_name'
	if [  "${page}" ] || ! [[ $page == '' ]];then
		if [ $ctrl == 'frontend' ] || [ $ctrl == 'backend' ]; then
			if [ ! -d "modules/$module/templates_twig/front" ]
			then 
				mkdir modules/$module/templates_twig/front
				echo $green'modules/'$module'/templates_twig/front'
			fi
			if [ $ctrl == 'frontend' ]; then
				if [ -f modules/$module/templates_twig/front/$page".htm" ]; then
				echo $red'Errore: la pagina '$page'.htm giè esiste'
				else
					echo '{% extends "layouts/page.htm" %}
{% block content %}

{% endblock %}' 	> modules/$module/templates_twig/front/$page".htm"
				echo $green'modules/'$module'/templates_twig/front/'$page'.htm'
				fi
				page_name=$page
			fi
			if [ $ctrl == 'backend' ]; then
				if [ -f modules/$module/templates_twig/front/$page".htm" ]; then
					echo $red'Errore: la pagina '$page'.htm giè esiste'
				else
					echo '{% extends 'layouts/page_backend.htm' %}
{% block backend_header %}
	<!---IMAGE<img src="">-->{{tr("titolo pagina '$page'","'$module'")}}
{% endblock %}
{% block backend_content %}

{% endblock %}' > modules/$module/templates_twig/front/$page".htm"
					echo $green'modules/'$module'/templates_twig/front/'$page'.htm'
					page_name=$page
				fi
			fi
		fi
	fi;

	case $ctrl in
		'frontend')
			if [ -f modules/$module/controllers/front/$nameCtrl"Controller.php" ]; then
				echo $red'Errore: il controller '$nameCtrl' già esiste'
				exit;
			fi

			echo '<?php
use Marion\Controllers\FrontendController;
class '$nameCtrl'Controller extends FrontendController{	

	public function display(){

		$this->setVar("variabile","contenuto variabile"); //per passare dati al template
		$this->output("'$page_name'.htm");
	}
}
?>' > modules/$module/controllers/front/$nameCtrl"Controller.php"
	echo $green'modules/'$module'/controllers/front/'$nameCtrl'Controller.php'
			;;
		'backend')
				if [ -f modules/$module/controllers/front/$nameCtrl"Controller.php" ]; then
					echo $red'Errore: il controller '$nameCtrl' già esiste'
					exit;
				fi
				echo '<?php
use Marion\Controllers\BackendController;
class '$nameCtrl'Controller extends BackendController{	
	public $auth=""; //permesso per accedere al controller

	public function display(){
		$this->setMenu("'$nameCtrl'_meu"); //attiva la voce di menu del backend.

		$this->setVar("variabile","contenuto variabile"); //per passare dati al template
		$this->output("'$page_name'.htm");
	}
}
?>' > modules/$module/controllers/front/$nameCtrl"Controller.php"
	echo $green'modules/'$module'/controllers/front/'$nameCtrl'Controller.php'
			;;
		'admin')
			if [ -f modules/$module/controllers/admin/$nameCtrl"Controller.php" ]; then
				echo $red'Errore: il controller '$nameCtrl' già esiste'
				exit;
			fi
			echo '<?php
use Marion\Controllers\ModuleController;
class '$nameCtrl'Controller extends ModuleController{	
	public $auth=""; //permesso per accedere al controller

	public function display(){
		$this->setMenu("'$nameCtrl'_meu"); //attiva la voce di menu del backend.

		$this->setVar("variabile","contenuto variabile"); //per passare dati al template
		$this->output("'$page_name'.htm");
	}
}
?>' > modules/$module/controllers/admin/$nameCtrl"Controller.php"
	echo $green'modules/'$module'/controllers/admin/'$nameCtrl'Controller.php'
			;;
		'adminList')
			if [ -f modules/$module/controllers/front/$nameCtrl"Controller.php" ]; then
				echo $red'Errore: il controller '$nameCtrl' già esiste'
				exit;
			fi
			echo '<?php
use Marion\Controllers\AdminModuleController;
use Illuminate\Database\Capsule\Manager as DB;
class '$nameCtrl'Controller extends AdminModuleController{	
	public $auth=""; //permesso per accedere al controller

	/*
	*	displayContent
	*	Metodo richiamato nel caso in cui $action è diverso da "add","edit" e da "list"
	*/
	public function displayContent(){
		
	}

	/*
	*	displayForm
	*	Metodo richiamato per mostrare il form
	*/
	public function displayForm(){
		$this->setMenu("'$nameCtrl'_edit"); //attiva la voce di menu del backend.
		$action = $this->getAction(); // valori ammessi "add","edit"

		if( $this->isSubmitted() ){
			//il form è stato sottomesso

			//prendo i dati del POST
			$data = $this->getFormdata();
			
			//controllo i dati con un form di controllo opportunamente creato
			$check = $this->checkDataForm("form-control-name",$data);

			
			if( $check[0] == "nak"){
				//se ci sono errori passo l errore al template
				$this->errors[] = $check[1];
			}else{
				//salvataggio dati
				if( $action != "add" ){
					// insert
				}else{
					$data = null;
				}
			}
		}else{

			if( $action != "add" ){
				// popolo il form con i dati presenti nel db
				$data = [];
			}else{
				$data = null;
			}
		}
		

		$dataform = $this->getDataForm("form-control-name",$data);
		$this->setVar("dataform",$dataform);
		$this->output("form.htm"); 
	}

	/*
	*	displayList
	*	Metodo richiamato per mosstrare la lista
	*/
	public function displayList(){
		$this->setMenu("'$nameCtrl'_list"); //attiva la voce di menu del backend.
		$containter = $this->getListContainer();
		
		$fields = [

			[
				"name" => "ID",
				"field_value" => "id",
				"searchable" => true,
				//"sortable" => true,
				"sort_id" => "id",
				"search_name" => "id",
				"search_value" => "",
				"search_type" => "input",
			],
			[
				"name" => "value 2",
				"field_value" => "value",
				"searchable" => true,
				//"sortable" => true,
				"sort_id" => "id",
				"search_name" => "id",
				"search_value" => "",
				"search_type" => "select",
				"search_options" => [
					"key1" => "value1",
					"key2" => "value2",
				]
			],
		];

		$container->setFieldsFromArray($fields);

		parent::displayList();
	}

	/*
	*	ajax
	*	Metodo richiamato per gestire le chiamate ajax
	*/
	public function ajax(){
		
	}


	/*
	*	delete
	*	Metodo richiamato per eliminare un elemento
	*/
	public function delete(){
		$id = $this->getId();

		$toReturn = [
			"deleted" => 1

		];
		$this->redirectToList($toReturn);
		
	}

	/*
	*	bulk
	*	Metodo richiamato per eseguire un azione bulk sugli elementi della lista selezionati
	*/
	public function bulk(){
		$action = $this->getBulkAction(); //prendo il valore selezionato per la bulk action
		$ids = $this->getBulkIds(); // prendo gli id selezionati 


		switch($action){
			case "action1":
				break;
			case "action2":
				break;
		}
		parent::bulk();
		
	}


}
?>' > modules/$module/controllers/admin/$nameCtrl"Controller.php"
	echo $green'modules/'$module'/controllers/admin/'$nameCtrl'Controller.php'
			;;
	esac

}

init(){
	mkdir $dir
	wget 'http://repository.3d0.it/marion.zip'
	mv marion.zip $dir
	cd $dir
	mkdir cache
	mkdir minimized
	unzip marion.zip
	rm -f marion.zip
	echo "/mysql
/minimized
/cache
.env" > .gitignore
	docker run --name $dir'_composer' -v $(pwd):/app -w /app/ webdevops/php-apache-dev:$PHP_VERSION composer install
	docker rm -f $dir'_composer'

	docker run --name $dir'_db' -v $(pwd)/mysql:/var/lib/mysql mysql:5.7.31 mysql -u $DB_USER -p$DB_PASS $DB_NAME < database.sql 2>/dev/null
	docker rm -f $dir"_db"
	git init
	echo $green' Il tuo progetto marion è stato installato con successo. Buon divertimento!'
	exit
}

cmd=`echo $1 | sed 's/ *$//g'`
if [ $cmd != 'init' ];
then
	envup
fi


#override php con eseguibile in container
if [ $APP_MODE != 'live' ];then
	container_web=$dirname"_web_1"
	alias php="docker exec -w /app $container_web php"
else
	#verifico se è stato specificato un percordo per l'eseguibile php
 	if [ $PHP_PATH ]; then
		alias php=$PHP_PATH
	fi
fi

case $cmd in
  'php-info')
	php -i
	;;
  'test')
	docker run --name $dirname'_composer' -v $(pwd):/app -w /app/ webdevops/php-apache-dev:$PHP_VERSION composer install
	docker rm -f $dirname'_composer'
	;;
  'clone')
	;;
  'init')
		dir=$2
		while [[ "$dir" == "" ]]
		do
			echo "Errore: devi specificare il nome del progetto ([a-zA-Z_])"
			read -p "Nome del progetto: " dir
		done
		init
		
   ;;
  'compile-scss')
		php lib/console/build_css.php
	;;
  'clean-cache')
		rm -rf cache/*
		rm -rf minimized/*
		echo $green'cache svuotata'
	;;
  'migrate')
		php lib/console/migrate.php $2 $3
    ;;
  'make')
	 entity=$2
	 case $entity in
	 	'model')
		 	nameModel=$3
			if [[ "$3" == "" ]] || [[ " $3 " == *"--module="* ]] || [[ " $3 " == *"--type="* ]] ;
			then
				echo $red"Errore: occorre specificare il nome del model"
				exit
			fi
			module=''
			type=''
			
		 	if [[ " ${args[*]} " == *"--type="* ]];
			then
				model=$(get_type_ctrl)
			fi
			if [[ " ${args[*]} " == *"--module="* ]];
			then
				module=$(get_mod_name)
			fi


			if [ "$module" != '' ] && [ "$model" != '' ]; then
				
				create_model
			else
				if [ "$module" == '' ];
				then
					echo $red"Errore: occorre specificare un modulo (--module=nome_modulo)"
					exit
				fi
				if [ "$type" == '' ];
				then
					echo $red"Errore: occorre specificare il tipo di model (--type=base|eloquent)"
					exit
				fi
			fi
			;;
		 'controller')
		 	nameCtrl=$3
			if [[ "$3" == "" ]] || [[ " $3 " == *"--module="* ]] || [[ " $3 " == *"--type="* ]] ;
			then
				echo $red"Errore: occorre specificare il nome del controller"
				exit
			fi
		 	module=''
			type=''
			page=''
		 	if [[ " ${args[*]} " == *"--type="* ]];
			then
				ctrl=$(get_type_ctrl)
			fi
			if [[ " ${args[*]} " == *"--module="* ]];
			then
				module=$(get_mod_name)
			fi

			if [[ " ${args[*]} " == *"--page="* ]];
			then
				page=$(get_page_name)
			fi

			if [ "$module" != '' ] && [ "$ctrl" != '' ]; then
				
				create_ctrl
			else
				if [ "$module" == '' ];
				then
					echo $red"Errore: occorre specificare un modulo (--module=nome_modulo)"
					exit
				fi
				if [ "$type" == '' ];
				then
					echo $red"Errore: occorre specificare il tipo di controller (--type=admin|adminList|frontend|backend|api)"
					exit
				fi
			fi

		;;
		'module')
			tag=$3
			create_module
		;;
		'page')
			name=$3
			if [[ "$3" == "" ]] || [[ " $3 " == *"--module="* ]] || [[ " $3 " == *"--type="* ]] ;
			then
				echo $red"Errore: occorre specificare il nome della pagina"
				exit
			fi
		 	module=''
			page=''
		 	if [[ " ${args[*]} " == *"--type="* ]];
			then
				page=$(get_type_ctrl)
			fi
			if [[ " ${args[*]} " == *"--module="* ]];
			then
				module=$(get_mod_name)
			fi

		

			if [ "$module" != '' ] && [ "$page" != '' ]; then
				
				create_page
			else
				if [ "$module" == '' ];
				then
					echo $red"Errore: occorre specificare un modulo (--module=nome_modulo)"
					exit
				fi
				if [ "$page" == '' ];
				then
					echo $red"Errore: occorre specificare il tipo di pagina (--type=page|backend|widget|base|form)"
					exit
				fi
			fi
			
		;;
		'migration')
			php lib/console/make_migration.php $3 $4
		;;
	 esac
  ;;
  'module')
   	action=$2
	case $action in
		 'create')
		 	tag=$3
			create_module
		 ;;
 		 'install')
			php lib/console/module_install.php $3
			if [[ " ${args[*]} " == *"--seed"* ]];
			then
				php lib/console/module_seeder.php $3
			fi
		  ;;
		  'uninstall')
			php lib/console/module_uninstall.php $3
		  ;;
		  'reset')
			php lib/console/module_uninstall.php $3
			php lib/console/module_install.php $3
			if [[ " ${args[*]} " == *"--seed"* ]];
			then
				php lib/console/module_seeder.php $3
			fi
		  ;;
		  'seed')
			php lib/console/module_seeder.php $3
		  ;;
		  'active')
			php lib/console/module_active.php $3
		  ;;
		  'disable')
			php lib/console/module_disable.php $3
		  ;;

		  'list')
			php lib/console/module_list.php $3 $4
		  ;;
	esac
	;;
  'dump-autoload')
	module=$2
	if [ $module ]
	then
		if [ ! -d "modules/$module" ]
		then 
			echo "Errore: il modulo non esiste"
			exit;
		fi
		if [ ! -f "modules/$module/composer.json" ]
		then 
			echo "Errore: il modulo non presenta un file composer.json"
			exit;
		fi
		docker exec -w /app/modules/$2 $dirname"_web_1" composer dump-autoload -o 
	fi
   ;;
  'up')
	  case $2 in
		   '--detach')
		   	 docker-compose up --detach
			;;
		   '-d')
			 docker-compose up --detach
			;;
			*)
			docker-compose up
			;;
		esac
	   ;;
  'down')
	  docker-compose down
	  ;;
   'open')
	  case $2 in
		   'db')
			 docker exec -ti $dirname"_db_1" bash
			;;
			'web')
			docker exec -ti -w /app/ $dirname"_web_1" bash
			;;
		esac
	   ;;
  'share')
	   $NGROK_PATH http $APACHE_PORT_EXPOSED
  	   ;;
  'db')
	  case $2 in
		   'import')
		   	  check=''
			  while [[ "$check" == "" ]]
			  do
					echo "Sicuro di volere importare il database?"
					read -p "Specificare un valore (s/n): " check
			  done
			  if [ $check == 's' ];	then
				 docker exec -i $dirname"_db_1" mysql -u $DB_USER -p$DB_PASS $DB_NAME < database.sql 2>/dev/null
			  	 echo $green'database importato'
			  fi
			;;
			'refresh')
			  check=''
			  while [[ "$check" == "" ]]
			  do
					echo "Sicuro di volere ricaricare il database?"
					read -p "Specificare un valore (s/n): " check
			  done
			  if [ $check == 's' ];	then
				docker exec -i $dirname"_db_1" mysql -u $DB_USER -p$DB_PASS -e "DROP DATABASE IF EXISTS $DB_NAME" 2>/dev/null
				docker exec -i $dirname"_db_1" mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE $DB_NAME" 2>/dev/null
				docker exec -i $dirname"_db_1" mysql -u $DB_USER -p$DB_PASS $DB_NAME < database.sql 2>/dev/null
				echo $green'database ricaricato'
			  fi
			  
			;;
		    'deploy')
			 
				mysql -u $DB_USER -p$DB_PASS -e "DROP DATABASE IF EXISTS $DB_NAME" 2>/dev/null
				mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE $DB_NAME" 2>/dev/null
				mysql -u $DB_USER -p$DB_PASS $DB_NAME < database.sql 2>/dev/null
				echo $green'database ricaricato'
			;;
			'export')
			  docker exec -ti $dirname"_db_1" mysqldump --no-tablespaces -u $DB_USER -p$DB_PASS $DB_NAME > database_tmp.sql
			  tail -n +2 database_tmp.sql > database.sql
		      rm database_tmp.sql 2>/dev/null
			  echo $green'database esportato in database.sql'
			;;
		esac
	    ;;
  'doctor')
	   chmod -R 777 cache
	   mkdir -p minimized
	   chmod -R 777 minimized
	   mkdir -p upload
	   chmod -R 777 upload
	   mkdir -p media
	   mkdir -p media/images
	   mkdir -p media/files
	   chmod -R 777 media
	   mkdir -p modules/pagecomposer/media
	   mkdir -p modules/pagecomposer/media/js
	   mkdir -p modules/pagecomposer/media/css
	   chmod -R 777 modules/pagecomposer/media
	   echo "completato"
       ;;
  '--version')
	  cat config/env.php | while read line 
		do
			if [[ $line == *_MARION_VERSION_* ]]
			then
				echo "$line" | sed -e "s/[a-zA-Z_\)\/;\',\()]//g"
			fi
		done
		;;
  '-v')
		cat config/env.php | while read line 
		do
			if [[ $line == *_MARION_VERSION_* ]]
			then
				echo "$line" | sed -e "s/[a-zA-Z_\)\/;\',\()]//g"
			fi
		done
	;;
  'export')
		if [ -d "esportazione" ]; then
			rm -r -f esportazione
		fi
		mkdir esportazione
		docker exec -ti $dirname"_db_1" mysqldump --no-tablespaces -u $DB_USER -p$DB_PASS $DB_NAME > esportazione/database_tmp.sql
		tail -n +2 esportazione/database_tmp.sql > esportazione/database.sql
		rm esportazione/database_tmp.sql 2>/dev/null
		cp -R media esportazione/media
		cp -R upload esportazione/upload
		tar -cvf esportazione.tar esportazione
		rm -r -f esportazione
	;;
esac