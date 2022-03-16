<?php
use Marion\Controllers\BackendController;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Marion;
use Elearning\{CourseUnit,CourseDetail};
class CourseController extends BackendController{


    public function index(){
        $this->setMenu('elearning_courses');
        $user = Marion::getUser();
        $orders = DB::table('cart','c')
        ->join('cartRow as r','r.cart','=','c.id')
        //->whereIn('c.status',['confirmed'])
        ->where('c.user',$user->id)->get(['product'])->toArray();
        foreach($orders as $o){
            $product = Product::withId($o->product);
            $products[] = $product;
        }
        $this->setVar('products',$products);
        $this->output('courses.htm');
    }

    public function view($id){
        $this->setMenu('elearning_courses');
        $user = Marion::getUser();
      
        $product = Product::withId($id);

        $units = CourseUnit::prepareQuery()->where('course_id',$id)->orderBy('order_view','ASC')->get();
        $details = CourseDetail::prepareQuery()
					->where('course_id',$product->id)
					->getOne();
        $this->setVar('details',$details);
        $this->setVar('units',$units);
        $this->setVar('product',$product);
        $this->output('course.htm');
    }
}
?>