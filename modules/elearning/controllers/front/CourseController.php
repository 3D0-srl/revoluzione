<?php
use Marion\Controllers\BackendController;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Marion;
use Elearning\{CourseUnit,CourseDetail};
use Marion\Components\WidgetComponent;
class CourseController extends BackendController{

    

    public function index(){
        $this->setMenu('elearning_courses');
        $user = Marion::getUser();
        $orders = DB::table('cart','c')
        ->join('cartRow as r','r.cart','=','c.id')
        ->whereIn('c.status',elearnig_status_cart())
        ->where('c.user',$user->id)->get(['product'])->toArray();
        foreach($orders as $o){
            $product = Product::withId($o->product);
            $products[] = $product;
        }
        $this->setVar('products',$products);
        $this->output('courses.htm');
    }

    public function view($id){
        $this->checkAccessCourse($id);
        $this->setMenu('elearning_courses');
        $user = Marion::getUser();
      
        $product = Product::withId($id);

        $units = CourseUnit::prepareQuery()->where('course_id',$id)->orderBy('order_view','ASC')->get();
        $details = CourseDetail::prepareQuery()
					->where('course_id',$product->id)
					->getOne();
        $this->setVar('details',$details);
       
        $this->setVar('product',$product);

        ob_start();
        $widget = new WidgetComponent('elearning');
		$units = CourseUnit::prepareQuery()->where('course_id',$product->id)->orderBy('order_view','ASC')->get();

		$widget->setVar('disabled',false);
		$widget->setVar('product',$product);
        
        $widget->setVar('units',$units);
		$widget->output('units.htm');
        $html = ob_get_contents();
        ob_end_clean();
        $this->setVar('units',$html);



        $this->output('course.htm');
    }

    public function video($course,$unit){
        $this->checkAccessCourse($course);
        $this->setMenu('elearning_courses');
        $product = Product::withId($course);
        $this->setVar('product',$product);

        $unit = CourseUnit::withId($unit);
        $this->setVar('unit',$unit);

        $token = $this->randomString(50);
        file_put_contents(_MARION_MODULE_DIR_.'elearning/tokens/'.$token,'');
        $this->setVar('token',$token);
        
        
        $this->output('video.htm');
    }


    public function support(){
        $formdata = [];
        if( $this->isSubmitted() ){
            $formdata = $this->getFormdata();
            $check = $this->checkDataForm('elearning_support',$formdata);
            if( $check[0] == 'ok'){
                $this->displayMessage(_translate("Messaggio inviato con successo!","elearning"));
            }else{
                $this->errors[] = $check[1];
            }
            //debugga($formdata);exit;
        }
        //debugga($check);exit;
        $dataform = $this->getDataForm('elearning_support',$formdata);
        $this->setVar('dataform',$dataform);
        $this->setMenu('elearning_support');
		$this->output('support.htm');
		
	}

    public function randomString($len=6){
		
		$result = "";
		$chars = 'abcdefghijklmnopqrstuvwxyz-0123456789';
		$charArray = str_split($chars);
		for($i = 0; $i < $len; $i++){
			$randItem = array_rand($charArray);
			$result .= "".$charArray[$randItem];
		}
		return $result;
		
	}

    private function checkAccessCourse($id){
        $check = elearning_check_course($id);
        if( !$check ){
            header('Location: '._MARION_BASE_URL_."index.php");
            die();
        }
        
        return $check;
    }
}
?>