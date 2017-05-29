<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Auth; 
use Modules\Admin\Models\User;
use Modules\Admin\Models\Category;
use Modules\Admin\Models\Product;
use Modules\Admin\Models\Transaction;
use View;
use Html;
use URL; 
use Validator; 
use Paginate;
use Grids; 
use Form;
use Hash; 
use Lang;
use Session;
use DB;
use Route;
use Crypt;
use Redirect;
use Cart;
use Input;
use App\Helpers\Helper as Helper;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

      public function __construct(Request $request) { 
        
        View::share('category_name',$request->segment(2));
        View::share('total_item',Cart::content()->count());
        View::share('sub_total',Cart::subtotal()); 
        View::share('userData',$request->session()->get('current_user'));

        $hot_products   = Product::orderBy('views','desc')->limit(3)->get();
        $special_deals  = Product::orderBy('discount','desc')->limit(3)->get(); 
        View::share('hot_products',$hot_products);
        View::share('special_deals',$special_deals);  
 

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       // $categories = Category::nested()->get();

        return view('home'); 


        $html =  Category::renderAsHtml(); 

        $categories =  Category::attr(['name' => 'categories'])
                        ->selected([3])
                        ->renderAsDropdown();
          return view('category',compact('categories','html')); 

    } 

    public function category(Request $request)
    {

        $btn = $request->get('submit_btn');

        if($btn=="Add Category")
        {
            $name = $request->get('sub_cat');
            $slug = str_slug($request->get('sub_cat'));
            $parent_id = 0;
            $cat = new Category;
            $cat->name = title_case($request->get('sub_cat'));
            $cat->slug = strtolower(str_slug($request->get('sub_cat')));
            $cat->parent_id = $request->get('categories');
            $cat->save();            
        }
        if($btn=="Add Sub Category")
        {
            $name = $request->get('sub_cat');
            $slug = str_slug($request->get('sub_cat'));
            $parent_id = $request->get('categories');

            $cat = new Category;

            $cat->name = title_case($request->get('sub_cat'));
            $cat->slug = strtolower(str_slug($request->get('sub_cat')));
            $cat->parent_id = $request->get('categories');

            $cat->save();
        }
        $categories =  Category::attr(['name' => 'categories'])
                        ->selected([3])
                        ->renderAsDropdown();

       $html =  Category::renderAsHtml(); 

       return view('category',compact('categories','html'));

  
    }


    public function home()
    {
        $banner_path1   = asset('public/enduser/assets/images/sliders/01.jpg');
        $banner_path2   = asset('public/enduser/assets/images/sliders/02.jpg');
 
        return view('end-user.home', compact('banner_path1', 'banner_path2'));
    }
 /*----------*/
    public function checkout()
    {
         $request = new Request;

        
        $products = Product::with('category')->orderBy('id','asc')->get();
        $categories = Category::nested()->get(); 
        return view('end-user.checkout',compact('categories','products','category'));   
    }

     /*----------*/
    public function productCategory( $category=null, $name=null,$id=null)
    { 
         $request = new Request;
         $q = Input::get('q'); 
       // dd($cat);
        $products = Product::with('category')->where('product_category',$name)->orderBy('id','asc')->get();
        if($products->count()==0)
        {
             $cat =  Category::where('parent_id',$name)->get(['id']);

             foreach ($cat as $key => $value) {
               $id[] = $value->id;
             }

              $products = Product::with('category')->whereIn('product_category',$id) 
                            ->orderBy('id','asc')
                            ->get();
             if($q)
             {
                $products = Product::with('category')->whereIn('product_category',$id)
                            ->where('product_title','LIKE','%'.$q.'%')
                            ->orderBy('id','asc')
                            ->get();
       
             }

             
        } 
        $categories = Category::nested()->get(); 
        return view('end-user.category',compact('categories','products','category','q','category'));   
    }
    /*----------*/
    public function productDetail($id=null)
    {   
        
        $product = Product::with('category')->where('id',$id)->first();
        $categories = Category::nested()->get(); 


        
        if($product==null)
        {
             $url =  URL::previous().'?error=InvaliAcess'; 
              return Redirect::to($url);
        }else{
          $product->views=$product->views+1;
          $product->save(); 
        }
         
        return view('end-user.product-details',compact('categories','product')); 
    }
     /*----------*/
    public function order(Request $request)
    { 
        $cart = Cart::content();
        $products = Product::with('category')->orderBy('id','asc')->get();
        $categories = Category::nested()->get(); 
        return view('end-user.order',compact('categories','products','category','cart'));   
         
    }
     /*----------*/
    public function faq()
    {
         $products = Product::with('category')->orderBy('id','asc')->get();
        $categories = Category::nested()->get(); 
        return view('end-user.faq',compact('categories','products','category')); 
        return view('end-user.faq');   
    }
      /*----------*/
    public function aboutus()
    {
         $products = Product::with('category')->orderBy('id','asc')->get();
        $categories = Category::nested()->get(); 
        return view('end-user.about',compact('categories','products','category')); 
        return view('end-user.about');   
    }
     /*----------*/
    public function trackOrder()
    {
         $products = Product::with('category')->orderBy('id','asc')->get();
        $categories = Category::nested()->get(); 
        return view('end-user.track-orders',compact('categories','products','category')); 
        return view('end-user.track-orders');   
    }
     /*----------*/
    public function tNc()
    {
         $products = Product::with('category')->orderBy('id','asc')->get();
        $categories = Category::nested()->get(); 
        return view('end-user.terms-conditions',compact('categories','products','category')); 
        return view('end-user.terms-conditions');   
    }
}
