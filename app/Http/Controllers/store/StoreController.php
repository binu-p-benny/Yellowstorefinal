<?php

namespace App\Http\Controllers\store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\admin\Mst_store;
use App\Models\admin\Mst_store_product;
use App\Models\admin\Sys_store_order_status;
use App\Models\admin\Mst_categories;
use App\Models\admin\Mst_store_product_varient;
use App\Models\admin\Mst_attribute_group;
use App\Models\admin\Mst_store_agencies;
use App\Models\admin\Mst_business_types;
use App\Models\admin\Mst_attribute_value;
use App\Models\admin\Mst_product_image;
use App\Models\admin\Mst_store_link_agency;
use App\Models\admin\Mst_order_link_delivery_boy;
use App\Models\admin\Country;
use App\Models\admin\State;
use App\Models\admin\District;
use App\Models\admin\Trn_store_order;
use App\Models\admin\Trn_store_order_item;
use App\Models\admin\Mst_delivery_boy;
use App\Models\admin\Trn_order_invoice;

use App\Models\admin\Mst_dispute;
use App\Models\admin\Mst_Tax;
use App\Models\admin\Town;
use App\Models\admin\Trn_store_setting;
use App\Models\admin\Trn_StoreTimeSlot;
use App\Models\admin\Trn_DeliveryBoyLocation;
use App\Models\admin\Trn_DeliveryBoyDeviceToken;

use App\Models\admin\Mst_store_documents;
use App\Models\admin\Mst_store_images;
use App\Models\admin\Mst_store_link_delivery_boy;

use App\Models\admin\Trn_GlobalProductVideo;
use App\Models\admin\Mst_GlobalProducts;
use App\Models\admin\Trn_GlobalProductImage;


use App\Models\admin\Trn_StoreAdmin;
use App\Models\admin\Trn_StoreDeliveryTimeSlot;
use App\Models\admin\Trn_store_payment_settlment;

use App\Models\admin\Trn_ProductVariantAttribute;
use App\Models\admin\Mst_SubCategory;
use App\Models\admin\Mst_Video;
use App\Models\admin\Trn_RecentlyVisitedStore;
use App\Models\admin\Trn_CustomerDeviceToken;
use App\Models\admin\Trn_StoreDeviceToken;
use App\Models\admin\Trn_configure_points;
use App\Models\admin\Trn_customer_reward;

use App\Helpers\Helper;


use App\Models\admin\Trn_store_customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Response;
use Image;
use DB;
use Hash;
use Carbon\Carbon;
use Crypt;
use Mail;
use PDF;


use App\Models\admin\Mst_StockDetail;
use App\Models\admin\Trn_ProductVideo;



class StoreController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth:store');
    }
    
    public function transfer()
    {
    echo "this will copy all store informations to storeadmin table";die;
    $store = Mst_store::all();
    //dd($store);
        foreach($store as $s)
        {
            $insert['store_id'] = $s->store_id;
            $insert['admin_name'] = $s->store_name;
            $insert['email'] = $s->email;
            $insert['username'] = $s->store_username;
            $insert['store_mobile'] = $s->store_mobile;
            $insert['role_id'] = 0;
            $insert['store_account_status'] = $s->store_account_status;
            $insert['password'] = $s->password;
            $insert['subadmin_id'] = $s->subadmin_id;

            
            if(Trn_StoreAdmin::create($insert))
            $arr[] = 'yes';
        }   
    echo count($arr);
    
    
    }


    public function index()
    {


    	$pageTitle = "Store";

    	$user_id =   Auth::guard('store')->user()->store_id;
    	
    	
    $storeProductData = Mst_store_product::select('product_cat_id')->where('store_id','=',$user_id)->orderBy('product_id','DESC')->get()->unique('product_cat_id')->pluck('product_cat_id')->toArray();

                    $catCount = Mst_categories::whereIn('category_id',$storeProductData)->count();
                    
                  //  $data['categoriesCount'] = $catCount; 
                    
                    

    	$store = Mst_store::where('store_id','=',$user_id)->get();
      $product = Mst_store_product::join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')
      ->where('mst_store_products.store_id',$user_id)->orderBy('mst_store_products.product_id', 'DESC')->count();


      $order = Trn_store_order::where('store_id','=',$user_id)->get()->count();
      $agency = Mst_store_link_agency::where('store_id','=',$user_id)->get()->count();
      $total_sale = Trn_store_order::where('store_id','=',$user_id)->sum('product_total_amount');
     // echo Carbon::today();die;
      $today_sale = Trn_store_order::where('store_id','=',$user_id)->whereDate('created_at', Carbon::today())->sum('product_total_amount');
      $today_sale_count = Trn_store_order::where('store_id','=',$user_id)->whereDate('created_at', Carbon::today())->count();
    
      $delivery_boys = Mst_delivery_boy::where('store_id','=',$user_id)->count();
      $dispute = \DB::table("mst_disputes")->where('store_id','=',$user_id)->count();
      $dispute_current = \DB::table("mst_disputes")->where('dispute_status','=',2)->where('store_id','=',$user_id)->count();
      $dispute_new = \DB::table("mst_disputes")->where('dispute_status','=',2)->where('store_id','=',$user_id)->whereDate('created_at', Carbon::today())->count();
    $deliveryBoys =  Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
    ->where('mst_store_link_delivery_boys.store_id',$user_id)->count();
    
    
    $recentvisitCountToday = Trn_RecentlyVisitedStore::whereDate('created_at', Carbon::today())
                        ->where('store_id',$user_id)->count();
    
    $recentvisitCountWeek = Trn_RecentlyVisitedStore::where( 'created_at', '>', Carbon::now()->subDays(2))
                        ->where('store_id',$user_id)->count();
    
    $recentvisitCountMonth = Trn_RecentlyVisitedStore::where( 'created_at', '>', Carbon::now()->subDays(30))
                        ->where('store_id',$user_id)->count();

    	return view('store.home',compact('recentvisitCountToday',
    	'deliveryBoys','catCount','dispute_new','dispute_current',
    	'dispute','delivery_boys','today_sale_count','today_sale',
    	'total_sale','store','pageTitle','product'
    	,'order','agency','recentvisitCountWeek','recentvisitCountMonth'
    	));
    }

    public function changePassword()
    {

        $pageTitle = "Update Password";
        $user_id = Auth::guard('store')->user()->store_id;

        $user = Mst_store::where('store_id','=',$user_id)->first();

        return view('store.elements.password.update_password',compact('pageTitle','user'));
    }

     public function updatePassword(Request $request, Mst_store $store)
    {

            $store_id = Auth::guard('store')->user()->store_id;

            $store = Mst_store::Find($store_id);

            $validator = Validator::make($request->all(),
            [
                'password'         => 'required|same:password_confirmation',

            ],
            [
                'password.required'        => 'Password required',



            ]);
          // $this->uploads($request);
            if(!$validator->fails())
            {
            $data= $request->except('_token');


              if (Hash::check($request->old_password, $store->password)) { 
                $data2= [
                  'password'      => Hash::make($request->password),
                      
                    ];
                Mst_store::where('store_id',$store_id)->update($data2);
                    Trn_StoreAdmin::where('store_id',$store_id)->update($data2);

              
              }
              else
              {
                  return redirect()->back()->with('errstatus','Old password incorrect.');

              }
              return redirect()->back()->with('status','Password updated successfully.');

        }else
        {

            return redirect()->back()->withErrors($validator)->withInput();
        }


  }

    public function Profile()
    {

        $pageTitle = "Update Profile";
        $store_id = Auth::guard('store')->user()->store_id;


        $store = Mst_store::where('store_id', '=', $store_id)->first();
        $countries = Country::all();
        $store_documents  = Mst_store_documents::where('store_id','=',$store_id)->get();
        $store_images = Mst_store_images::where('store_id','=',$store_id)->get();
        $agencies = Mst_store_link_agency::where('store_id','=',$store_id)->get();
    
            $delivery_boys = Mst_store_link_delivery_boy::where('store_id','=',$store_id)->get();
    
           
                $all_delivery_boys = \DB::table('mst_delivery_boys')
                ->join('mst_stores','mst_stores.store_id','=','mst_delivery_boys.store_id')
                ->where('mst_stores.subadmin_id',$store->subadmin_id)
                ->get();
           
    
            $delivery_boys = Mst_store_link_delivery_boy::where('store_id','=',$store_id)->get();
        $business_types = Mst_business_types::where('business_type_status','=',1)->get();
    




        // $store = Mst_store::where('store_id','=',$user_id)->first();
        // $countries = Country::all();
        return view('store.elements.update_profile',compact('all_delivery_boys','store','pageTitle',
        'countries','store_images','store_documents','agencies','delivery_boys','business_types'));
    }





    public function updateProfile(Request $request, Mst_store $store)
    {
  
        

    	 $store_Id = $request->store_id;

       $store_id = Auth::guard('store')->user()->store_id;
    	 
    	 $store = Mst_store::Find($store_id);


    	$validator = Validator::make($request->all(),
		[
		    'store_name'    => 'required|unique:mst_stores,store_name,'.$store_id.',store_id',
			'store_contact_person_name'        => 'required',
			'store_contact_person_phone_number'=> 'required',
			'store_pincode'				       => 'required',
			'store_primary_address'            => 'required',
			'store_country_id'			       => 'required',
		//	'profile_image'			       => 'required',
			'store_state_id'       		       => 'required',
			//'email'       		       => 'required',


			//'store_commision_amount'			       => 'required',

			'store_district_id'                => 'required',
		//	'store_username'   => 'required|unique:mst_stores,store_username,'.$store_id.',store_id',
			'store_username'   => 'required',
			//'store_commision_percentage' =>'required',


         ],
		[
		    'profile_image.required'         				 => 'Store profile image required',
		    'store_name.required'         				 => 'Store name required',
			'store_contact_person_name.required'     	 => 'Contact person name required',
			'store_contact_person_phone_number.required' => 'Contact person number required',

              //  'email.required'         				 => 'Email required',

			'store_pincode.required'        			 => 'Pincode required',
			'store_primary_address.required'             => 'Primary address required',
			'store_country_id.required'         		 => 'Country required',
			'store_state_id.required'        			 => 'State required',
			'store_district_id.required'        		 => 'District  required',
			'store_username.required'        			 => 'Username required',
			//'store_commision_amount.required'                => 'Store commision amount required',

			//'store_commision_percentage.required'	=>'Store commision percentage required',



		]);


		     if ($request->hasFile('store_document_other_file')) {

                $doc_validate = Validator::make($request->all(),
                [
                    'store_document_other_file.*'        => 'mimes:pdf,doc,docx,txt',
                ],
                [
                    'store_document_other_file.*.mimes' => "store documents file format error",
                    ]);
                    if($doc_validate->fails())
                    {
		            return redirect()->back()->withErrors($doc_validate)->withInput();

                    }

            }
            
        


            if ($request->hasFile('store_image')) {

                $img_validate = Validator::make($request->all(),
                [
                    'store_image.*' => 'required|dimensions:min_width=1000,min_height=800',
                ],
                [
                    'store_image.*.dimensions' => 'store image dimensions invalid',
                    ]);
                    if($img_validate->fails())
                    {
		            return redirect()->back()->withErrors($img_validate)->withInput();

                    }

            }

        if(!$validator->fails())
		{
     	$data= $request->except('_token');
     //	dd($store);
     	$filenamePro = $store->profile_image;
     	if ($request->hasFile('profile_image')) {
        
            $filePro = $request->file('profile_image');
            $filenamePro = rand(1,5000).time() . '.' . $filePro->getClientOriginalExtension();
            $filePro->move('assets/uploads/store_images/images', $filenamePro);

        }
        
        
    $senderAddrs = Str::of($request->store_place)->replace(' ', '+');
    $sendrJson = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$senderAddrs&key=AIzaSyBSqyoP-FHj6nJpuIvNYmb1YaGqBmh3xdQ");
    
    $json =json_decode($sendrJson);
      
      $sendLat =$json->results[0]->geometry->location->lat;
      $sendLong =$json->results[0]->geometry->location->lng;
      $sendPlaceId =$json->results[0]->place_id;
      
    //dd($json,$sendLat,$sendLong,$sendPlaceId);
    //     dd($request->store_place);


       $data= [

        'store_name' =>  $request->store_name,
        'store_name_slug' => Str::of($request->store_name)->slug('-'),
        'store_contact_person_name' => $request->store_contact_person_name,
        'store_mobile' => $request->store_mobile,
        'store_contact_person_phone_number' => $request->store_contact_person_phone_number,
        'store_website_link' => $request->store_website_link,
        'store_pincode' => $request->store_pincode,
        'store_primary_address' => $request->store_primary_address,
        'email' => $request->email,
        'store_country_id' => $request->store_country_id,
        'store_state_id' => $request->store_state_id,
        'store_district_id' => $request->store_district_id,
        'business_type_id' => $request->business_type_id,
        'store_username' => $request->store_username,
        'store_commision_percentage' => $request->store_commision_percentage,
        'store_commision_amount' => $request->store_commision_amount,
        'town_id' => $request->store_town,
        'place' => $request->store_place,
        'profile_image' => $filenamePro,
        'latitude' => $sendLat,
        'longitude' => $sendLong,
        'place_id' => $sendPlaceId,
        //'store_qrcode' => $request->store_qrcode,
        'upi_id' => $request->upi_id,

       ];

      //  \QrCode::format('svg')->size(500)->generate($request->store_qrcode,'assets/uploads/store_qrcodes/'.$request->store_qrcode.'.svg');


       Mst_store::where('store_id',$store_id)->update($data);
	

		$date = Carbon::now();
 		if ($request->hasFile('store_document_other_file')) {



            $allowedfileExtension = ['pdf', 'doc', 'txt',];
                  $files = $request->file('store_document_other_file');
                  $files_head = $request->store_document_other_file_head;
                    $k = 0;
                  foreach ($files as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();

                    $file->move('assets/uploads/store_document/files', $filename);

                        $data1= [[
                                'store_id'               => $store_id,
                            'store_document_license'  => $request->store_document_license,
                            'store_document_gstin'     => $request->store_document_gstin,
                            'store_document_file_head' => $files_head[$k],
                            'store_document_other_file' => $filename,
                            'created_at'         		=> $date,
                            'updated_at'         		=> $date,
                                    ],
                                  ];

                             Mst_store_documents::insert($data1);
                             $k++;

                }
              }


		// multiple image upload

		        if($request->hasFile('store_image'))
                {



                    $store_image = $request->file('store_image');
                   // dd($product_image);
                    foreach($store_image as $image)
                    {
                        $filename = rand(1,5000).time().'.'.$image->getClientOriginalExtension();
                       // dd($filename);
                        $destination_path = 'assets/uploads/store_images/images';

                        $store_img = Image::make($image->getRealPath());
                        $store_img->save($destination_path.'/'.$filename,80);



                            $data2= [[
                                'store_image'      => $filename,
                                'store_id' 			=> $store_id,
                                'created_at'         => $date,
                                'updated_at'         => $date,
                                    ],
                                  ];

                             Mst_store_images::insert($data2);

                    }
                }

        return redirect('store/home')->with('status','Profile updated successfully.');
	}else
	{

		return redirect()->back()->withErrors($validator)->withInput();
	}



}

  public function destroyStore_Doc(Request $request,Mst_store_documents $document)
    {


      $document =  $document->delete();

      return redirect()->back()->with('status','Document deleted successfully');
    }

    public function destroyStore_Image(Request $request,Mst_store_images $image)
    {
  
      $image = $image->delete();
  
      return redirect()->back()->with('status','Image deleted Successfully');;
    }
  

     public function listProduct(Request $request)
    {



      try {
        

        $pageTitle = "Products";
        $store_id =  Auth::guard('store')->user()->store_id;
        $products = Mst_store_product::
            join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')
        ->where('mst_store_products.store_id',$store_id)->orderBy('mst_store_products.product_id', 'DESC')->get();
        //dd($products);
        $store = Mst_store::all();

      if($_GET){

    //    echo "here";die;

    //dd($request->all());
        $product_name = $request->product_name;
        $product_code = $request->product_code;
        $stock_status =  $request->stock_status;
        $product_status =  $request->product_status;

        $a1 = Carbon:: parse($request->From_date)->startOfDay();
        $a2 = Carbon:: parse($request->To_date)->endOfDay();
        $b1 = $request->start_price;
        $b2 = $request->end_price;

        // $a[] = Carbon:: parse($request->From_date)->startOfDay();
        // $a[] = Carbon:: parse($request->To_date)->endOfDay();
        // $b[] = $request->start_price;
        // $b[] = $request->end_price;

        // $products = Mst_store_product::where('product_name','like', '%'.$product_name.'%')
        //     ->where('product_code','like', '%'.$product_code.'%')
        //     ->where('stock_status','like', '%'.$stock_status.'%')
        //     ->where('product_status','like', '%'.$product_status.'%')
        //     ->where('store_id','like', '%'.$store_id.'%')
        //     ->whereBetween('created_at',[$a,$a])
        //      ->whereBetween('product_price',[$b,$b])
        //     ->get();

            DB::enableQueryLog();
       // print( Auth::guard('store')->user()->store_id);die;

    	$store_id =   Auth::guard('store')->user()->store_id;


         $query =  Mst_store_product:: join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')
        ->where('mst_store_products.store_id',$store_id)->orderBy('mst_store_products.product_id', 'DESC');
             

            if(isset($request->product_name))
            {
                $query = $query->where('mst_store_products.product_name','LIKE', '%'.$product_name.'%');
            }

            if(isset($request->From_date) && isset($request->To_date))
            {
                $query = $query->whereBetween('created_at',[$a1,$a2]);
            }

            if(isset($request->start_price) && isset($request->end_price))
            {
                $query = $query->whereBetween('product_price_offer',[$b1,$b2]);
            }

            if(isset($request->start_price) && !isset($request->end_price))
            {
                $query = $query->where('product_price_offer','>=',$b1);
            }

            if(!isset($request->start_price) && isset($request->end_price))
            {
                $query = $query->where('product_price_offer','<=',$b2);
            }

            if(isset($product_code))
            {
                $query = $query->where('product_code','LIKE', '%'.$product_code.'%');
            }

            // if(isset($stock_status))
            // {
            //   if($stock_status != 0){
            //     $query = $query->where('stock_count','!=' ,0);
            //   }else{
            //     $query = $query->where('stock_count',0);
            //   }
            // }

            if(isset($product_status))
            {
                $query = $query->where('product_status',$product_status);
            }


            $productsz = $query->get();
            
            $products = array();
             if(isset($stock_status))
            {
               if($stock_status == 0){

                foreach($productsz as $key => $product){
                  $stock_count_sum = \DB::table('mst_store_product_varients')->
                  where('product_id',$product->product_id)->
                  sum('stock_count');
                  if($stock_count_sum == 0){
                  $products[] = $product;
                  }
                }
                
               }else{
                foreach($productsz as $key => $product){
                  $stock_count_sum = \DB::table('mst_store_product_varients')->
                  where('product_id',$product->product_id)->
                  sum('stock_count');
                  if($stock_count_sum > 0){
                    $products[] = $product;

                  }
                }
               }
            }
            else
            {
              $products = $productsz;
            }
            
            
//dd(DB::getQueryLog());

              return view('store.elements.product.list',compact('products','pageTitle','store'));
            }

        return view('store.elements.product.list',compact('products','pageTitle','store'));

      } catch (\Exception $e) {
      
        return redirect()->back()->withErrors(['Something went wrong!'])->withInput();

    }
 }


public function createProduct()
{
     $pageTitle = "Create Products";

    $products = Mst_store_product::all();
    $attr_groups = Mst_attribute_group::all();
    $tax = Mst_Tax::all();

    $colors = Mst_attribute_value::join('mst_attribute_groups','mst_attribute_groups.attr_group_id','=','mst_attribute_values.attribute_group_id')
    ->where('mst_attribute_groups.group_name','LIKE','%color%')
    ->select('mst_attribute_values.*')
    ->get();
    $agencies = Mst_store_agencies::where('agency_account_status',1)->get();
    $category = Mst_categories::where('category_status',1)->get();
    
  $store_id =  Auth::guard('store')->user()->store_id;
    $products_global_products_id = Mst_store_product::where('store_id',$store_id)
    ->where('global_product_id','!=', null)
    ->orderBy('product_id', 'DESC')
    ->pluck('global_product_id')
    ->toArray();

    $global_product = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id)->get();

    $business_types = Mst_business_types::all();
    $store = Mst_store::all();

    return view('store.elements.product.create',compact('category','global_product','agencies','colors','tax','products','pageTitle','attr_groups','store','business_types'));
}

public function GetAttr_Value(Request $request)
    {
        $grp_id = $request->attr_group_id;
       // dd($grp_id);
        $attr_values  = Mst_attribute_value::where("attribute_group_id",'=',$grp_id)
        ->pluck("group_value","attr_value_id");


        return response()->json($attr_values);

    }
 public function GetCategory(Request $request)
    {
         $business_id = $request->business_type_id;

        $category  = Mst_categories::where("business_type_id",'=',$business_id)->where('category_status',1)->pluck("category_name","category_id");
         return response()->json($category);

    }
       public function GetSubCategory(Request $request)
    {
         $category_id = $request->category_id;

        $subcategory  = Mst_SubCategory::where("category_id",'=',$category_id)->where('sub_category_status',1)->pluck("sub_category_name","sub_category_id");
         return response()->json($subcategory);

    }

public function storeProduct(Request $request,Mst_store_product $product,Mst_store_product_varient $varient_product, Mst_product_image $product_img)
{
    //dd($request->all());
    $store_id =  Auth::guard('store')->user()->store_id;

    // if(isset($request->product_name))
    // {
    //   $s = DB::table('mst_store_products')
    //   ->where('product_name','LIKE', '%'.$request->product_name.'%')
    //   ->where('store_id',$store_id)
    //   ->groupBy('product_name')->count();
    //   if($s > 0)
    //   {
    //     return redirect()->back()->withErrors(['store' => 'Product name already exist'])->withInput();
    //   }
    // }


    $validator = Validator::make($request->all(),
        [
           // 'product_name'          => 'required|unique:mst_store_products,product_name,'.$store_id.',store_id',
            'product_name'          => 'required',
            'product_description'   => 'required',
            'regular_price'   => 'required',
            'sale_price'   => 'required',
            'tax_id'   => 'required',
            'min_stock'   => 'required',
            'product_code'   => 'required',
           // 'business_type_id'   => 'required',
            //'attr_group_id'   => 'required',
           // 'attr_value_id'   => 'required',
            'product_cat_id'   => 'required',
            'vendor_id'   => 'required',
           // 'color_id'   => 'required',
           // 'product_image.*' => 'required|dimensions:min_width=1000,min_height=800',
            'product_image.*' => 'required',



         ],
        [

            'product_name.required'             => 'Product name required',
            'product_name.unique'             => 'Product name already exist',
            'product_description.required'      => 'Product description required',
            'regular_price.required'      => 'Regular price required',
            'sale_price.required'      => 'Sale price required',
            'tax_id.required'      => 'Tax required',
            'min_stock.required'      => 'Minimum stock required',
            'product_code.required'      => 'Product code required',
            'business_type_id.required'        => 'Product type required',
            'attr_group_id.required'        => 'Attribute group required',
            'attr_value_id.required'        => 'Attribute value required',
            'product_cat_id.required'        => 'Product category required',
            'vendor_id.required'        => 'Vendor required',
            'color_id.required'        => 'Color required',
            'product_image.required'        => 'Product image required',
            'product_image.dimensions'        => 'Product image dimensions invalid',


      ]);

    if(!$validator->fails())
       {


        $product->product_name           = $request->product_name;
        $product->product_description    = $request->product_description;
        $product->product_price          = $request->regular_price;
        $product->product_price_offer    = $request->sale_price;
        $product->tax_id                 = $request->tax_id; // new
        $product->stock_count                 = $request->min_stock; // stock count
        $product->min_stock                 = $request->min_stock; // stock count
        $product->sub_category_id   = $request->sub_category_id; // new
        
        //$product->product_code          = "PRDCT00"; // old
        $product->product_code           = $request->product_code;
        
        if($request->business_type_id)
        $product->business_type_id       = $request->business_type_id; // product type
        else
        $product->business_type_id       = 0;

        if($request->color_id)
        $product->color_id               = $request->color_id; 
        else
        $product->color_id       = 0;

        //$product->attr_group_id          = $request->attr_group_id;
       // $product->attr_value_id          = $request->attr_value_id;
        $product->product_cat_id         = $request->product_cat_id;
        $product->vendor_id              = $request->vendor_id; // new
        $product->product_brand              = $request->product_brand; // new

        $product->product_name_slug      = Str::of($request->product_name)->slug('-');
       // $product->product_specification  = $request->product_specification;  // removed
        $product->store_id               = $store_id;
        $product->global_product_id               =  @$request->global_product_id; // new
     
     
        $product->product_type               =  @$request->product_type; // new
        if($request->product_type == 2)
        $product->service_type               =  @$request->service_type; // new
        else
        $product->service_type               =  0; // new

       // $product->product_offer_from_date = $request->product_offer_from_date;
       // $product->product_offer_to_date   = $request->product_offer_to_date;
       // $product->product_delivery_info   = $request->product_delivery_info;
       //  $product->product_shipping_info   =$request->product_shipping_info;

        if($request->min_stock == 0)
        {
            $product->stock_status = 0;
        }else
        {
             $product->stock_status = 1;
        }

        $product->product_status         = 1;




           $product->save();
            $id = DB::getPdo()->lastInsertId();


                  if ($request->hasFile('product_image')) {
                  $allowedfileExtension = ['jpg', 'png', 'jpeg',];
                  $files = $request->file('product_image');
                  $c = 1;
                  foreach ($files as $file) {



                 //   $filename = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();

                   // $fullpath = $filename . '.' . $extension ;
                    $file->move('assets/uploads/products/base_product/base_image', $filename);
                       $date = Carbon::now();
                        $data1= [[
                                'product_image'      => $filename,
                                'product_id' => $id,
                                'product_varient_id' => 0,
                                'image_flag'         => 0,
                                'created_at'         => $date,
                                'updated_at'         => $date,
                                    ],
                                  ];

                             Mst_product_image::insert($data1);
                             
                        $proImg_Id = DB::getPdo()->lastInsertId();

                    if($c == 1)
                    {
                        DB::table('mst_store_products')->where('product_id', $id)->update(['product_base_image' => $filename]);
                        $c++;
                        DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);


                    }

                }
              }

              $date = Carbon::now();
              $vc = 0;

              foreach ($request->variant_name as  $varName) {

                  if(isset($varName)) {
                      $sCount = 0;
                        if($request->product_type == 2)
                        {
                            $sCount = 1;
                        }


                        $data3 = [
                          'product_id' => $id,
                          'store_id' => $store_id,
                          'variant_name' => $request->variant_name[$vc],
                          'product_varient_price' => $request->var_regular_price[$vc],
                          'product_varient_offer_price' => $request->var_sale_price[$vc],
                          'product_varient_base_image' => null,
                          'stock_count' => $sCount,
                          'color_id' =>  0,
                          'created_at' => $date,
                          'updated_at' => $date,
                          // 'attr_group_id' => $request->attr_group_id[$vc],
                          // 'attr_value_id' => $request->attr_value_id[$vc],
                        ];

                        Mst_store_product_varient::create($data3);
                        $vari_id = DB::getPdo()->lastInsertId();
                        
                        $vac = 0;

                          foreach($request->attr_group_id[$vc] as $attrGrp)
                          {
                            $data4 = [
                              'product_varient_id' => $vari_id,
                              'attr_group_id' => $attrGrp,
                              'attr_value_id' => $request->attr_value_id[$vc][$vac],
                            ];
                            Trn_ProductVariantAttribute::create($data4);
                            $vac++;
                          }

                          $vic = 0;
                        // dd( $request->file('var_images'.$vc));
                          if(isset( $request->file('var_images')[$vc]) )
                          {

                          $files = $request->file('var_images')[$vc];
                        //dd($files);
                            foreach ($files as $file) 
                              {
                             //   $filename = $file->getClientOriginalName();
                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();

                                $extension = $file->getClientOriginalExtension();
                                  $file->move('assets/uploads/products/base_product/base_image', $filename);
                                  $date = Carbon::now();
                                  
                                  $data5= [[
                                          'product_image'      => $filename,
                                          'product_id' => $id,
                                          'product_varient_id' => $vari_id,
                                          'image_flag'         => 0,
                                          'created_at'         => $date,
                                          'updated_at'         => $date,
                                              ],
                                            ];
                                    Mst_product_image::insert($data5);
                        $proImg_Id = DB::getPdo()->lastInsertId();

                                if($vic == 0)
                                {
                                  DB::table('mst_store_product_varients')->where('product_varient_id', $vari_id)->update(['product_varient_base_image' => $filename]);
                                  $vic++;
                                    DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);

                              }
                            }
                          }
                            $vc++;
                        }
                    
              }
              
                $countVariants = Mst_store_product_varient::where('product_id',$id)->count();
              $varImages = Mst_product_image::where('product_id',$id)->orderBy('product_image_id','DESC')->get();
           
              if($countVariants < 1)
              {
                $date = Carbon::now();
                
                   $sCount = 0;
                    if($request->product_type == 2)
                    {
                        $sCount = 1;
                    }

                 $data3 = [
                          'product_id' => $id,
                          'store_id' => $store_id,
                          'variant_name' => $request->product_name,
                          'product_varient_price' => $request->regular_price,
                          'product_varient_offer_price' => $request->sale_price,
                          'product_varient_base_image' => null,
                          'stock_count' => $sCount,
                          'color_id' =>  0,
                          'created_at' => $date,
                          'updated_at' => $date,
                        ];
                Mst_store_product_varient::create($data3);
                $vari_id = DB::getPdo()->lastInsertId();
                
                $vic = 0;

                foreach($varImages as $vi)
                {
                   
                     $data77= [[
                                'product_image'      => $vi->product_image,
                                'product_id' => $id,
                                'product_varient_id' => $vari_id,
                                'image_flag'         => 0,
                                'created_at'         => Carbon::now(),
                                'updated_at'         => Carbon::now(),
                                              ],
                                            ];
                    Mst_product_image::insert($data77);
                    $proImg_Id = DB::getPdo()->lastInsertId();

                    if($vic == 0)
                    {
                        DB::table('mst_store_product_varients')->where('product_varient_id', $vari_id)->update(['product_varient_base_image' => $vi->product_image]);
                        $vic++;
                        DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);

                    }
                    
                }
              }


         return redirect('store/product/list')->with('status','Product added successfully.');

        }else
            {

                return redirect()->back()->withErrors($validator)->withInput();
            }

}

    public function setDefaultImage(Request $request)
    {
        //dd($request->all());
        $imageData = Mst_product_image::where('product_image_id',$request->product_image_id)->where('product_varient_id',$request->product_varient_id)->first();

        Mst_product_image::where('product_id',$imageData->product_id)->where('product_varient_id',$request->product_varient_id)->update(['image_flag'=>0]);

        if($request->product_varient_id == 0)
        {
           
            Mst_product_image::where('product_image_id',$request->product_image_id)->where('product_varient_id',$request->product_varient_id)->update(['image_flag'=>1]);
            Mst_store_product::where('product_id',$imageData->product_id)->update(['product_base_image' => $imageData->product_image]);
            return true;
        }
        else
        {
            Mst_product_image::where('product_image_id',$request->product_image_id)->where('product_varient_id',$request->product_varient_id)->update(['image_flag'=>1]);
            Mst_store_product_varient::where('product_varient_id',$imageData->product_varient_id)->update(['product_varient_base_image' => $imageData->product_image]);
            return true;
        }
                return false;
    
    }




public function viewProduct(Request $request, $id)
    {
        $pageTitle = "View Product";

        $product = Mst_store_product::where('product_id', '=',$id)->first();
        $product_id = $product->product_id;
        $product_varients = Mst_store_product_varient::where('product_id',$product_id)
        ->orderBy('product_varient_id','DESC')
        ->get();
      // $varient_product = Mst_store_product_varient::where('product_id', '=',$product_id)->first();

       //$product_varient_id = $varient_product->product_varient_id;

       // $attr_groups = Mst_attribute_group::all();
        $product_images = Mst_product_image::where('product_id','=',$product_id)->get();

        //dd($product_images);

     //   $store = Mst_store::all();
       // $categories = Mst_categories::where([['category_status', '=', '1'],['parent_id', '==', '0'],])->whereIn('category_id',['1','4','9'])->get();


        return view('store.elements.product.view',compact('product_varients','product','pageTitle','product_images'));

    }
    
    
 public function editProduct(Request $request, $id)
    {
        $pageTitle = "Edit Product";

        $product = Mst_store_product::where('product_id', '=',$id)->first();
        $product_id = $product->product_id;
        
          $product_varients = Mst_store_product_varient::where('product_id',$product_id)
      ->orderBy('product_varient_id','DESC')
      ->get();
      @$category_id = $product->product_cat_id;
      $subcategories = Mst_SubCategory::where('category_id',@$category_id)->where('sub_category_status',1)->get();
     // dd($subcategories);

      // $varient_product = Mst_store_product_varient::where('product_id', '=',$product_id)->first();

      // $product_varient_id = $varient_product->product_varient_id;
       $business_types = Mst_business_types::all();
        $attr_groups = Mst_attribute_group::all();
        $product_images = Mst_product_image::where('product_id','=',$product_id)->get();
        $tax = Mst_Tax::all();
        $category = Mst_categories::where('category_status',1)->get();

        $colors = Mst_attribute_value::join('mst_attribute_groups','mst_attribute_groups.attr_group_id','=','mst_attribute_values.attribute_group_id')
        ->where('mst_attribute_groups.group_name','LIKE','%color%')
        ->select('mst_attribute_values.*')
        ->get();
    $agencies = Mst_store_agencies::where('agency_account_status',1)->get();


        $store = Mst_store::all();

        return view('store.elements.product.edit',compact('subcategories','product_varients','category','agencies','colors','tax','product','pageTitle','attr_groups','store','product_images','business_types'));

    }
    
    
     public function GetGlobal_Product(Request $request)
    {
        $vendor_id = $request->vendor_id;
        
        $store_id =  Auth::guard('store')->user()->store_id;

        $products_global_products_id = Mst_store_product::where('store_id',$store_id)
        ->where('global_product_id','!=', null)
        ->orderBy('product_id', 'DESC')
        ->pluck('global_product_id')
        ->toArray();

        $global_product = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id)
       ->where('vendor_id',$vendor_id)->pluck("product_name","global_product_id");




        return response()->json($global_product);

    }

 public function destroyProductImage(Request $request, $product_image_id,Mst_product_image $pro_image)
 {
       // echo $product_image_id;die;
        $pro_image = Mst_product_image::where('product_image_id','=',$product_image_id);
        $pro_image->delete();

        return redirect()->back()->with('status','Product Image Deleted Successfully.');


 }





public function updateProduct(Request $request,$product_id,Mst_store_product_varient $varient_product)
{

  $store_id =  Auth::guard('store')->user()->store_id;
   $product_id = $request->product_id;
        //echo $product_id;die;
        // $product = Mst_store_product::where($product_id);
       //  $varient_product = Mst_store_product_varient::where('product_id','=',$product_id)->first();
        //dd($product_id);

        // if(isset($request->product_name))
        // {
        //   $s = DB::table('mst_store_products')
        //   ->where('product_name','LIKE', '%'.$request->product_name.'%')
        //   ->where('store_id',$store_id)
        //   ->groupBy('product_name')->count();
        //   if($s > 0)
        //   {
        //     return redirect()->back()->withErrors(['store' => 'Product name already exist'])->withInput();
        //   }
        // }

        $validator = Validator::make($request->all(),
        [
           // 'product_name'          => 'required|unique:mst_store_products,product_name,'.$product_id.',product_id',
           'product_name'   => 'required',
           'product_description'   => 'required',
            'regular_price'   => 'required',
            'sale_price'   => 'required',
            'tax_id'   => 'required',
            'min_stock'   => 'required',
            'product_code'   => 'required',
          //  'business_type_id'   => 'required',
          //  'attr_group_id'   => 'required',
           // 'attr_value_id'   => 'required',
            'product_cat_id'   => 'required',
            'vendor_id'   => 'required',
           // 'color_id'   => 'required',
          //  'product_image.*' => 'dimensions:min_width=1000,min_height=800',



         ],
        [

          'product_name.required'             => 'Product name required',
          'product_name.unique'             => 'Product name already exist',
          'product_description.required'      => 'Product description required',
          'regular_price.required'      => 'Regular price required',
          'sale_price.required'      => 'Sale price required',
          'tax_id.required'      => 'Tax required',
          'min_stock.required'      => 'Minimum stock required',
          'product_code.required'      => 'Product code required',
          'business_type_id.required'        => 'Product type required',
          'attr_group_id.required'        => 'Attribute group required',
          'attr_value_id.required'        => 'Attribute value required',
          'product_cat_id.required'        => 'Product category required',
          'vendor_id.required'        => 'Vendor required',
          'color_id.required'        => 'Color required',
          'product_image.required'        => 'Product image required',
          'product_image.dimensions'        => 'Product image dimensions invalid',


      ]);

    if(!$validator->fails())
       {



        $product['product_name']          = $request->product_name;
        $product['product_description']    = $request->product_description;
        $product['product_price']         = $request->regular_price;
        $product['product_price_offer']    = $request->sale_price;
        $product['tax_id']                 = $request->tax_id; // new
        $product['stock_count']                = $request->min_stock; // stock count
        $product['product_code']          = $request->product_code;
        
        if(isset($request->business_type_id))
        $product['business_type_id']       = $request->business_type_id; // product type
        else
        $product['business_type_id']       = 0;

//$product['color_id']               = $request->color_id; // new

      //  $product['attr_group_id']         = $request->attr_group_id;
      // $product['attr_value_id']         = $request->attr_value_id;
        $product['product_cat_id']         = $request->product_cat_id;
        $product['vendor_id']            = $request->vendor_id; // new
        $product['product_brand']            = $request->product_brand; // new

        $product['sub_category_id']            = $request->sub_category_id; // new
        $product['product_type']            = $request->product_type; // new
        if($request->product_type == 2)
        $product['service_type']            = $request->service_type; // new
        else
        $product['service_type']            = 0; // new

        $product['product_name_slug']      = Str::of($request->product_name)->slug('-');
        $product['store_id']               = $store_id;

        if($request['min_stock'] == 0)
        {
            $product['stock_status'] = 0;
        }else
        {
             $product['stock_status'] = 1;
        }

       // $product['product_status'] = 0;


   DB::table('mst_store_products')->where('product_id', $product_id)->update($product);

// adding product images
            if ($request->hasFile('product_image')) {
               // echo "here";die;
                $allowedfileExtension = ['jpg', 'png', 'jpeg',];
                $files = $request->file('product_image');
                foreach ($files as $file) {



                 // $filename = $file->getClientOriginalName();
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();

                  $extension = $file->getClientOriginalExtension();

                 // $fullpath = $filename . '.' . $extension ;
                  $file->move('assets/uploads/products/base_product/base_image', $filename);
                     $date = Carbon::now();
                      $data1= [[
                              'product_image'      => $filename,
                              'product_id' => $product_id,
                              'product_varient_id' => 0,
                              'image_flag'         => 1,
                              'created_at'         => $date,
                              'updated_at'         => $date,
                                  ],
                                ];

                           Mst_product_image::insert($data1);



              }
            }


            $date = Carbon::now();
            $vc = 0;

            foreach ($request->variant_name as  $varName) {

                if(isset($varName)) { 

                    $sCount = 0;
                    if($request->product_type == 2)
                    {
                        $sCount = 1;
                    }
                      $data3 = [
                        'product_id' => $product_id,
                        'store_id' => $store_id,
                        'variant_name' => $request->variant_name[$vc],
                        'product_varient_price' => $request->var_regular_price[$vc],
                        'product_varient_offer_price' => $request->var_sale_price[$vc],
                        'product_varient_base_image' => null,
                        'stock_count' => $sCount,
                        'color_id' =>  0,
                        'created_at' => $date,
                        'updated_at' => $date,
                        // 'attr_group_id' => $request->attr_group_id[$vc],
                        // 'attr_value_id' => $request->attr_value_id[$vc],
                      ];

                      Mst_store_product_varient::create($data3);
                      $vari_id = DB::getPdo()->lastInsertId();
                      
                      $vac = 0;

                        foreach($request->attr_group_id[$vc] as $attrGrp)
                        {
                          $data4 = [
                            'product_varient_id' => $vari_id,
                            'attr_group_id' => $attrGrp,
                            'attr_value_id' => $request->attr_value_id[$vc][$vac],
                          ];
                          Trn_ProductVariantAttribute::create($data4);
                          $vac++;
                        }

                        $vic = 0;
                      // dd( $request->file('var_images'.$vc));
                        if(isset( $request->file('var_images')[$vc]) )
                        {

                        $files = $request->file('var_images')[$vc];
                      //dd($files);
                          foreach ($files as $file) 
                            {
                             // $filename = $file->getClientOriginalName();
                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();

                              $extension = $file->getClientOriginalExtension();
                                $file->move('assets/uploads/products/base_product/base_image', $filename);
                                $date = Carbon::now();
                                
                                $data5= [[
                                        'product_image'      => $filename,
                                        'product_id' => $product_id,
                                        'product_varient_id' => $vari_id,
                                        'image_flag'         => 1,
                                        'created_at'         => $date,
                                        'updated_at'         => $date,
                                            ],
                                          ];
                                  Mst_product_image::insert($data5);
                              if($vic == 0)
                              {
                                DB::table('mst_store_product_varients')->where('product_varient_id', $vari_id)
                                ->update(['product_varient_base_image' => $filename]);
                                $vic++;
                            }
                          }
                        }
                          $vc++;
                      }
                  
            }

            
            return redirect('store/product/list')->with('status','Product Updated Successfully.');

        }else
            {

                return redirect()->back()->withErrors($validator)->withInput();
            }

}





public function destroyProduct(Request $request,$product)
    {

               Mst_store_product::where('product_id',$product)->delete();

        Mst_store_product_varient::where('product_id',$product)->delete();

        return redirect('store/product/list')->with('status','Product deleted Successfully');
    }

public function statusProduct(Request $request,Mst_store_product $product,$product_id)
    {

         $pro_id = $request->product_id;
         $product = Mst_store_product::Find($pro_id);
         $status = $product->product_status;
         
         $varCount = Mst_store_product_varient::where('product_id',$product_id)->count();
        if($varCount > 0)
        {
            if($status == 0)
             {
                 $product->product_status  = 1;
             }else{
                $product->product_status  = 0;
             }
            $product->update();
     return redirect()->back()->with('status','Product Status Changed Successfully');

        }
        else{
            return redirect()->back()->with('err_status','No variant exists.');
        }
             

    }

    public function stockUpdate(Request $request,
                                 Mst_store_product $product, $product_id)
    {


        $product_id = $request->product_id;
        $product = Mst_store_product::Find($product_id);

        $validator = Validator::make($request->all(),
        [

            'stock_count'   => 'required',

         ],
        [
            'stock_count.required' => 'Status required',


        ]);
      // $this->uploads($request);
        if(!$validator->fails())
        {
        $data= $request->except('_token');


            $product->stock_count = $request->stock_count;
            if($request->stock_count == 0)
            {
                $product->stock_status = 0;
            }else
            {
                 $product->stock_status = 1;
            }

        $product->update();

        return redirect()->back()->with('status','Stock Updated successfully.');
    }else
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}



     public function listAgency(Request $request)
  {


    $pageTitle = "Agencies";
    $user_id =   Auth::guard('store')->user()->store_id;
    $agencies = Mst_store_link_agency::where('store_id','=',$user_id)->get();
    $countries = Country::all();

    return view('store.elements.agencies.list',compact('agencies','pageTitle','countries'));

    }

     public function  AssignAgency(Request $request)
    {

    $pageTitle = "Create Agency";

     $agencies = Mst_store_agencies::where('agency_account_status',1)->get();

    return view('store.elements.agencies.assign_agency',compact('agencies','pageTitle'));

    }

    public function storeAssignAgency(Request $request,Mst_store_link_agency $link_agency)
    {

      $validator = Validator::make($request->all(),
    [
        'agency_id'             => 'required',

         ],
    [
        'agency_id.required'       => 'Agency required',



    ]);

        if(!$validator->fails())
    {
      $data= $request->except('_token');

      $store_id = Auth()->guard('store')->user()->store_id;
      $date =  Carbon::now();
      $values = $request->agency_id;
        //dd($values);
      foreach($values as $value){

          $data = [[
             'agency_id'=>$value,
             'store_id'=>$store_id,
             'created_at'=> $date,
             'updated_at'=> $date,


          ],
            ];

    Mst_store_link_agency::insert($data);
    }

       return redirect('store/agency/list')->with('status','Agency Assigned successfully.');
  }else
  {

    return redirect()->back()->withErrors($validator)->withInput();
  }


    }
  public function CheckAgencyEmail(Request $request)
    {

      $email = $request->agency_email_address;

        $data = Mst_store_agencies::where('agency_email_address', $email)
          ->where('agency_account_status',1)->count();

        if($data >0)
        {
         echo 'not_unique';
        }
        else
        {
         echo 'unique';
        }
     }

     public function CheckAgencyUsername(Request $request)
    {

      $username = $request->agency_username;
        $data = Mst_store_agencies::where('agency_username', $username)
          ->where('agency_account_status',1)->count();

        if($data >0)
        {
          //dd()
         echo 'not_unique';
        }
        else
        {
         echo 'unique';
        }
     }
     public function GetState(Request $request)
    {
        $country_id = $request->country_id;
        //dd($country_id);
        $state = State::where("country_id",'=',$country_id)
        ->pluck("state_name","state_id");
        return response()->json($state);
    }

    public function GetTown(Request $request)
		{
				$city_id = $request->city_id;
				//dd($city_id);
				$town = Town::where("district_id",'=',$city_id)
				->pluck("town_name","town_id");
			//	echo $town;die;
			 	return response()->json($town);
		}

  public function GetCity(Request $request)
    {
        $state_id = $request->state_id;
        //dd($state_id);
        $city = District::where("state_id",'=',$state_id)
        ->pluck("district_name","district_id");
        return response()->json($city);
    }

     public function createAgency()
      {

          $pageTitle = "Create Agencies";
          $agencies = Mst_store_agencies::where('agency_account_status',1)->get();
          $countries   = Country::all();
          $business_types = Mst_business_types::all();

          return view('store.elements.agencies.create',compact('pageTitle','agencies','countries','business_types'));


      }


  public function storeAgency(Request $request, Mst_store_agencies $agency)
    {

  $validator = Validator::make($request->all(),
    [
        'agency_name'                 => 'required|unique:mst_store_agencies',
      'agency_contact_person_name'        => 'required',
      'agency_contact_person_phone_number'=> 'required',
   // 'agency_contact_number_2'           => 'required',
      'agency_website_link'             => 'required',
      'agency_pincode'            => 'required',
      'agency_primary_address'            => 'required',
      'agency_email_address'              => 'required',
      'country_id'                  => 'required',
      'state_id'                      => 'required',
      'district_id'                       => 'required',
      'agency_username'             => 'required|unique:mst_store_agencies',
      'agency_password'                 => 'required|min:5|same:password_confirmation',
      'agency_logo'             => 'required|mimes:jpeg,png,jpg,gif,svg'


         ],
    [
        'agency_name.required'                 => 'Agency name required',
      'agency_contact_person_name.required'        => 'Contact person name required',
      'agency_contact_person_phone_number.required' => 'Contact person Number required',
      //'agency_contact_number_2.required'            => 'Contact number 2 required',
      'agency_website_link.required'             => 'website Link required',
      'agency_pincode.required'              => 'Pincode required',
      'agency_primary_address.required'             => 'Primary address required',
      'agency_email_address.required'               => 'Email required',
      'country_id.required'                       => 'Country required',
      'state_id.required'                       => 'State required',
      'district_id.required'                      => 'District  required',
      'agency_username.required'                => 'Username required',
      'agency_password.required'            => 'Password required',
      'agency_logo.required'              =>'Agency logo required'


    ]);

        if(!$validator->fails())
    {
      $data= $request->except('_token');


    $agency->agency_name      = $request->agency_name;
    $agency->agency_name_slug     = Str::of($request->agency_name)->slug('-');
    $agency->agency_contact_person_name   = $request->agency_contact_person_name;
    $agency->agency_contact_person_phone_number =    $request->agency_contact_person_phone_number;
    $agency->agency_contact_number_2       = $request->agency_contact_number_2;
    $agency->agency_website_link           = $request->agency_website_link;
    $agency->agency_pincode                = $request->agency_pincode;
    $agency->agency_primary_address        = $request->agency_primary_address;
    $agency->agency_email_address          = $request->agency_email_address;
    $agency->country_id                    = $request->country_id;
    $agency->state_id                      = $request->state_id;
    $agency->district_id                   = $request->district_id;
    $agency->business_type_id              = $request->business_type_id;
    $agency->agency_username               = $request->agency_username;
    $agency->agency_password               = Hash::make($request->agency_password);
    $agency->agency_account_status         = 0;





  if($request->hasFile('agency_logo'))
    {
      $agency_logo = $request->file('agency_logo');


      $filename = rand(1,5000).time().'.'.$agency_logo->getClientOriginalExtension();

      $location = public_path('assets/uploads/agency/logos/'.$filename);

      Image::make($agency_logo)->save($location);
      $agency->agency_logo = $filename;

    }

      $agency->save();
        return redirect('store/agency/list')->with('status','Agency added successfully.');
  }else
  {

    return redirect()->back()->withErrors($validator)->withInput();
  }

  }

  public function listOrder(Request $request)
  {
 
    $pageTitle = "List Order";
    $store_id =   Auth::guard('store')->user()->store_id;
    $customer = Trn_store_customer::all();        

    $orders = Trn_store_order::where('store_id','=',$store_id)->orderBy('order_id', 'DESC')->paginate(10);
    $status = Sys_store_order_status::all();
    $store = Mst_store::all();
    $product = Mst_store_product::where('store_id','=',$store_id)->get();
    
    $delivery_boys = Mst_delivery_boy::join('mst_store_link_delivery_boys','mst_store_link_delivery_boys.delivery_boy_id','=','mst_delivery_boys.delivery_boy_id')
    ->select("mst_delivery_boys.*")->where('mst_store_link_delivery_boys.store_id',$store_id)->get();

 $assign_delivery_boys = Mst_delivery_boy::join('mst_store_link_delivery_boys','mst_store_link_delivery_boys.delivery_boy_id','=','mst_delivery_boys.delivery_boy_id')
    ->select("mst_delivery_boys.*")
    ->where('mst_delivery_boys.availability_status',1)
    ->where('mst_delivery_boys.delivery_boy_status',1)
    ->where('mst_store_link_delivery_boys.store_id',$store_id)->get();


 

   // dd($delivery_boys);
   
        if($_GET){
        
              $delivery_boy_id = $request->delivery_boy_id;
              $status_id = $request->status_id;
              $customer_id = $request->customer_id;
        
        
              $a1 = Carbon:: parse($request->date_from)->startOfDay();
              $a2  = Carbon:: parse($request->date_to)->endOfDay();
              DB::enableQueryLog();
        
            $query = Trn_store_order::where('store_id','=',$store_id);
        
            if(isset($request->status_id))
            {
               $query->where('status_id', $status_id);
             //  $query->orWhere('payment_status', $status_id);
            }
            if(isset($request->delivery_boy_id))
            {
               $query->where('delivery_boy_id',$delivery_boy_id);
            }
            if(isset($request->customer_id))
            {
               $query->where('customer_id',$customer_id);
            }
            if(isset($request->date_from) && isset($request->date_to))
            {
              $query->whereDate('created_at','>=',$a1)->whereDate('created_at','<=',$a2);
            }
                    $orders = $query->paginate(10);
                   // dd(DB::getQueryLog());
            return view('store.elements.order.list',compact('assign_delivery_boys','customer','orders','pageTitle','status','store','status','product','delivery_boys'));
          }
        return view('store.elements.order.list',compact('assign_delivery_boys','customer','orders','pageTitle','status','store','status','product','delivery_boys'));
    }
    
    
      public function listTodaysOrder(Request $request)
      {
     
        $pageTitle = "List Order";
        $store_id =   Auth::guard('store')->user()->store_id;
        $customer = Trn_store_customer::all();     
        
        $date_from = Carbon::now()->toDateString();
        $date_to = Carbon::now()->toDateString();
         $a1 = Carbon:: parse($date_from)->startOfDay();
            $a2  = Carbon:: parse($date_to)->endOfDay();
    
        $orders = Trn_store_order::where('store_id','=',$store_id)
        ->whereDate('created_at','>=',$a1)->whereDate('created_at','<=',$a2)
        ->orderBy('order_id', 'DESC')->get();
        
        $status = Sys_store_order_status::all();
        $store = Mst_store::all();
        $product = Mst_store_product::where('store_id','=',$store_id)->get();
        
        $delivery_boys = Mst_delivery_boy::join('mst_store_link_delivery_boys','mst_store_link_delivery_boys.delivery_boy_id','=','mst_delivery_boys.delivery_boy_id')
        ->select("mst_delivery_boys.*")->where('mst_store_link_delivery_boys.store_id',$store_id)->get();
    
      
            return view('store.elements.order.list',compact('date_to','date_from','customer','orders','pageTitle','status','store','status','product','delivery_boys'));
        }
    
    
    
    
    public function viewOrder(Request $request,$id)
    {
      try{ 
        $pageTitle = "View Order";
        $decrId  = Crypt::decryptString($id);
        $order = Trn_store_order::Find($decrId);
        $order_items = Trn_store_order_item::where('order_id',$decrId)->get();

        $product = $order->product_id;

        $subadmin_id = Auth()->guard('store')->user()->subadmin_id;
        $store_id = Auth()->guard('store')->user()->store_id;


          $delivery_boys = Mst_delivery_boy::join('mst_store_link_delivery_boys','mst_store_link_delivery_boys.delivery_boy_id','=','mst_delivery_boys.delivery_boy_id')
    ->select("mst_delivery_boys.*")->where('mst_store_link_delivery_boys.store_id',$store_id)->get();

        $customer = Trn_store_customer::all();
        $status = Sys_store_order_status::all();

        return view('store.elements.order.view',compact('delivery_boys','order_items','order','pageTitle','status','customer'));
        
        } catch (\Exception $e) {
          
            return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
      
        }
    }
    
    
    
    
    
     public function updateOrder(Request $request,$id)
    {
      try{ 

        //dd($request->all());

        foreach($request->order_item_id as $key => $value)
        {
          Trn_store_order_item::where('order_item_id',$value)->update(['tick_status' => $request->product[$key]]);
        }

        $data['delivery_boy_id']  = $request->delivery_boy_id;
        $data['status_id']  = $request->status_id;
        
           if($request->status_id == 7)
          {
            $data['delivery_status_id'] = 1;
          }
          
           else if($request->status_id == 8)
          {
            $data['delivery_status_id'] = 2;
          }
          
          else if($request->status_id == 9)
          {
            $data['delivery_status_id'] = 3;
          }
          else
          {
            $data['delivery_status_id'] = null;
          }

        
        
        $data['order_note']  = $request->order_note;

		    $query = Trn_store_order::where('order_id',$id)->update($data);

        return redirect()->back()->with('status','Order updated successfully.');

      } catch (\Exception $e) {
       // echo $e->getMessage();die;
        return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
      }
      
    }
    

  public function viewInvoice(Request $request,$id)
  {

    $pageTitle = "View Invoice";
    $decrId  = Crypt::decryptString($id);
    $order_id =$decrId;
    $order = Trn_store_order::Find($decrId);
    $customer = Trn_store_customer::all();
    $status = Sys_store_order_status::all();
    $order_items = Trn_store_order_item::where('order_id',$decrId)->get();
    $store_id = Auth::guard('store')->user()->store_id;
    $store_data = Mst_store::where('store_id',$store_id)->first();
   // dd($order_items);


    return view('store.elements.order.invoice',compact('store_data','order_id','order_items','order','pageTitle','status','customer'));

    }
      public function OrderStatus(Request $request, Trn_store_order $order, $order_id)
    {

     //try {

      
          $order_id = $request->order_id;
          $order = Trn_store_order::Find($order_id);
          $order_number = $order->order_number;
          $store_id = $order->store_id;
          $customer_id = $order->customer_id;

          $validator = Validator::make($request->all(),
          [

            'status_id'   => 'required',

              ],
          [
              'status_id.required' => 'Status required',


          ]);

            if(!$validator->fails())
        {
          $data= $request->except('_token');


          $order->status_id = $request->status_id;
          
           if($request->status_id == 8)
          {
               if($order->order_type == 'APP')
              {
                  if(($order->delivery_boy_id == 0) || !isset($order->delivery_boy_id))
                  {
                    return redirect()->back()->withErrors(['delivery boy not assigned']);
                  }
              }
              
          }
          
          if($request->status_id == 9)
          {
              $order->delivery_date = Carbon::now()->format('Y-m-d');
              $order->delivery_time = Carbon::now()->format('H:i');
              if($order->order_type == 'APP')
              {
                  if(($order->delivery_boy_id == 0) || !isset($order->delivery_boy_id))
                  {
                    return redirect()->back()->withErrors(['delivery boy not assigned']);
                  }
              }
              
                $configPoint = Trn_configure_points::find(1);
                $orderAmount  = $configPoint->order_amount;
                $orderPoint  = $configPoint->order_points;
                
                $orderAmounttoPointPercentage =  $orderAmount / $orderPoint;
                $orderPointAmount = ( $order->product_total_amount * $orderAmounttoPointPercentage) / 100;
                
                
                    if(Trn_store_order::where('customer_id',$customer_id)->count() == 1)
                    {
                         $configPoint = Trn_configure_points::find(1);

                        $cr = new Trn_customer_reward;
                        $cr->transaction_type_id = 0;
                        $cr->reward_points_earned = $configPoint->first_order_points;
                        $cr->customer_id = $customer_id;
                        $cr->order_id = $order_id;
                        $cr->reward_approved_date = Carbon::now()->format('Y-m-d');
                        $cr->reward_point_expire_date = Carbon::now()->format('Y-m-d');
                        $cr->reward_point_status = 1;
                        $cr->discription = "First order points";
                        $cr->save();
                        
                        $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();

                            foreach($customerDevice as $cd)
                            {
                                $title = 'Points creadited';
                                $body = 'First order points credited successully..';
                                $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                            }
                        
                        
                         // referal - point
                       $refCusData = Trn_store_customer::find($order->customer_id);
                       if($refCusData->referred_by)
                       {
                            $crRef = new Trn_customer_reward;
                            $crRef->transaction_type_id = 0;
                            $crRef->reward_points_earned = $configPoint->referal_points;
                            $crRef->customer_id = $refCusData->referred_by;
                            $crRef->order_id = null;
                            $crRef->reward_approved_date = Carbon::now()->format('Y-m-d');
                            $crRef->reward_point_expire_date = Carbon::now()->format('Y-m-d');
                            $crRef->reward_point_status = 1;
                            $crRef->discription = "Referal points";
                            $crRef->save();
                            
                            $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$refCusData->referred_by)->get();

                            foreach($customerDevice as $cd)
                            {
                                $title = 'Points creadited';
                                $body = 'Referal points credited successully..';
                                $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                            }
                            
                            
                            
                              // joiner - point
                                 $crJoin = new Trn_customer_reward;
                                $crJoin->transaction_type_id = 0;
                                $crJoin->reward_points_earned = $configPoint->joiner_points;
                                $crJoin->customer_id = $order->customer_id;
                                $crJoin->order_id = $order->order_id;
                                $crJoin->reward_approved_date = Carbon::now()->format('Y-m-d');
                                $crJoin->reward_point_expire_date = Carbon::now()->format('Y-m-d');
                                $crJoin->reward_point_status = 1;
                                $crJoin->discription = "Referal joiner points";
                                if($crJoin->save())
                                {
                                     $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$order->customer_id)->get();
            
                                        foreach($customerDevice as $cd)
                                        {
                                            $title = 'Points creadited';
                                            $body = 'Referal joiner points credited successully..';
                                            $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                                        }
                                }


                        }
                    
                    

                                                
                                                
                    }
                    
                
                if((Trn_customer_reward::where('order_id',$order_id)->count() < 1 ) || (Trn_store_order::where('customer_id',$customer_id)->count() == 1))
                {
                    $cr = new Trn_customer_reward;
                    $cr->transaction_type_id = 0;
                    $cr->reward_points_earned = $orderPointAmount;
                    $cr->customer_id = $customer_id;
                    $cr->order_id = $order_id;
                    $cr->reward_approved_date = Carbon::now()->format('Y-m-d');
                    $cr->reward_point_expire_date = Carbon::now()->format('Y-m-d');
                    $cr->reward_point_status = 1;
                    $cr->discription = null;
                    $cr->save();
                }
                
                   
          }
          
        if($request->status_id == 8)
          {
            $order->delivery_status_id = 2;
          }
           else if($request->status_id == 7)
          {
            $order->delivery_status_id = 1;
          }
          else if($request->status_id == 9)
          {
            $order->delivery_status_id = 3;
          }
          else
          {
            $order->delivery_status_id = null;
          }
          
          

          

          $status_id = $request->status_id;
          if($status_id == 1)
          {
              $order_status = "Pending";
              
                  $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                  $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order Pending';
                       $body = 'Your order with order id '.$order_number.' is pending..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }

              
              
          }elseif ($status_id == 2) {
              $order_status = "PaymentSuccess";
          }elseif ($status_id == 3) {
              $order_status = "Payment Cancelled";
          }elseif ($status_id == 4) {
              $order_status = "Confirmed";
              
              
                    $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                    $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order confirmed';
                       $body = 'Your order with order id '.$order_number.' is confirmerd..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }
                   
                   
          }elseif ($status_id == 5) {
              $order_status = "Cancelled";
              
                    $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                  $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order cancelled';
                       $body = 'Your order with order id '.$order_number.' is cancelled..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }
                   
          }
          elseif ($status_id == 6) {
              $order_status = "Completed";
              
              $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                  $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order completed';
                       $body = 'Your order with order id '.$order_number.' is completed..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }
                   
                   
          }
          elseif ($status_id == 7) {
              $order_status = "Ready for Delivery";
              
               $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                  $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order ready for delivery';
                       $body = 'Your order with order id '.$order_number.' is packed and ready for delivery..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }
                   
                   
          }
          elseif ($status_id == 8) {
              $order_status = "Out for Delivery";
              
               $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                  $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order out for delivery';
                       $body = 'Your order with order id '.$order_number.' is out for delivery..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }
                   
                   
          }else  {
              $order_status = "Deliverd";
              
               $storeDatas = Trn_StoreAdmin::where('store_id',$store_id)->where('role_id',0)->first();
                  $customerDevice = Trn_CustomerDeviceToken::where('customer_id',$customer_id)->get();
                  $storeDevice = Trn_StoreDeviceToken::where('store_admin_id',$storeDatas->store_admin_id)->where('store_id',$store_id)->get();
                   foreach($customerDevice as $cd)
                   {
                       $title = 'Order deliverd';
                       $body = 'Your order with order id '.$order_number.' is deliverd..';
                       $data['response'] =  $this->customerNotification($cd->customer_device_token,$title,$body);
                   }
                   
                   
          }

        $cus_id = $order->customer_id;

        $customer = Trn_store_customer::Find($cus_id);
        $customer_email = $customer->customer_email;
          //dd($customer_email);
          
          if($request->status_id == 5)
          {
            $orderData = Trn_store_order_item::where('order_id',$order_id)->get();
            

           // dd($orderData);
           foreach($orderData as $o)
            {
                
            $productVarOlddata = Mst_store_product_varient::find($o->product_varient_id);

                $sd = new Mst_StockDetail;
                   $sd->store_id = $store_id;
                   $sd->product_id = $o->product_id;
                   $sd->stock = $o->quantity;
                   $sd->product_varient_id = $o->product_varient_id;
                   $sd->prev_stock = $productVarOlddata->stock_count;
                   $sd->save();
           
                
              DB::table('mst_store_product_varients')->where('product_varient_id', $o->product_varient_id)->increment('stock_count',$o->quantity);
            }
          }
          
          
          $order->update();
          
         
          $data = array('order_number'=>$order_number,'order_status'=>$order_status,'to_mail'=>$customer_email);


            // Mail::send('store/mail-template/order-status-mail-template', $data, function($message) use ($data){
            //         $message->to($data['to_mail'], 'Yellowstore - Order Status')->subject
            //             ('ORDER-STATUS-UPDATION');
            //         $message->from('anumadathinakath@gmail.com','Customer-Order-Status');
            //     });

            return redirect()->back()->with('status','Status updated successfully.');
      }else
      {
        return redirect()->back()->withErrors($validator)->withInput();
      }
    // } catch (\Exception $e) {
      
    //   return redirect()->back()->withErrors(['Something went wrong!'])->withInput();

    // }
}


   private function customerNotification($device_id,$title,$body){
        $url = 'https://fcm.googleapis.com/fcm/send';
        $api_key = 'AAAA09gixf4:APA91bFiBdhtMnj2UBtqSQ9YlZ_uxvdOOOzE-otA9Ja2w0cFUpX230Xv0Yi87owPBlFDp1H02FWpv4m8azPsuMmeAmz0msoeF-1Cxx0iVpDSOjYBTCWxzUYT8tKTuUvLb08MDsRXHbgM';
        $fields = array(
            'to' => $device_id,
            'notification' => array('title' => $title, 'body' => $body, 'sound' => 'default'),
            );
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$api_key
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch); 
        return $result;
  }
 public function AssignOrder(Request $request, $id)
    {


        $pageTitle = "Assign Order to Delivery Boy";
        $store_id = Auth()->guard('store')->user()->store_id;
        $decrId  = Crypt::decryptString($id);
        $order = Trn_store_order::Find($decrId);
        $delivery_boys = Mst_delivery_boy::where('store_id','=',$store_id)->get();

    return view('store.elements.order.assign_order',compact('order','pageTitle','delivery_boys'));

    }

    public function storeAssignedOrder(Request $request,Mst_order_link_delivery_boy $link_delivery_boy)
    {


     $order_id = $request->order_id;
      $validator = Validator::make($request->all(),
    [
        'delivery_boy_id'             => 'required',

         ],
    [
        'delivery_boy_id.required'       => 'Delivery boy required',



    ]);

        if(!$validator->fails())
    {
      
      $data= $request->except('_token');
      
      $link_delivery_boy->order_id = $request->order_id;
      $link_delivery_boy->delivery_boy_id = $request->delivery_boy_id;
      $link_delivery_boy->save();
      
          $order = Trn_store_order::Find($request->order_id);
$order->delivery_boy_id =  $request->delivery_boy_id;
$order->delivery_accept =  null;
$order->save();

 $dBoyDevices = Trn_DeliveryBoyDeviceToken::where('delivery_boy_id',$request->delivery_boy_id)->get();
        
            foreach($dBoyDevices as $cd)
            {
                $title = 'Order Assigned';
                $body = 'New order('.$order->order_number.') arrived';
                $data =  Helper::deliveryBoyNotification($cd->dboy_device_token,$title,$body);
            }
      

       return redirect('store/order/list')->with('status','Order assigned successfully.');
  }else
  {

    return redirect()->back()->withErrors($validator)->withInput();
  }
}

 public function generatePdf(Request $request,$id)
 {


    $decrId  = Crypt::decryptString($id);
    $order = Trn_store_order::Find($decrId);
    $order_no = $order->order_number;
    $pageTitle = "Invoice";
    $order_id =   $decrId;
    $order_items = Trn_store_order_item::where('order_id',$decrId)->get();

    $store_id = Auth::guard('store')->user()->store_id;
    $store_data = Mst_store::where('store_id',$store_id)->first();

   // dd($order_no);

    $pdf = PDF::loadView('store.elements.order.bill', compact('store_data','order_id','order_items','order','pageTitle'));

  //return view('store.elements.order.bill',compact('order_items','pageTitle','order'));


    $content =  $pdf->download()->getOriginalContent();

    Storage::put('uploads\order_invoice\Ivoice_'.$order_no.'.pdf', $content);

    return $pdf->download('Ivoice_'.$order_no.'.pdf');

 }
 public function SendInvoice(Request $request,$id)
 {


    $decrId  = Crypt::decryptString($id);
    $order = Trn_store_order::Find($decrId);
    $order_no = $order->order_number;
    $cus_id = $order->customer_id;
    $customer = Trn_store_customer::where('customer_id','=',$cus_id)->first();
    $cus_mobile_number = $customer->customer_mobile_number;

    $file =  Storage::get('uploads\order_invoice\Ivoice_'.$order_no.'.pdf');


  dd($file);

 }

public function destroyAttribute(Request $request,Mst_attribute_group $attr_groups)
{
        $attr_groups->delete();

        return redirect()->back()->with('status','Attribute deleted successfully');
}


  public function storeAttribute(Request $request, Mst_attribute_group $attr_group)
    {

  $validator = Validator::make($request->all(),
    [
        'group_name'                 => 'required',


         ],
    [
        'group_name.required'                 => 'Group name required',


    ]);

        if(!$validator->fails())
    {
      $data= $request->except('_token');


    $attr_group->group_name      = $request->group_name;

      $attr_group->save();
        return redirect()->back()->with('status','Attribute added successfully.');
  }else
  {

    return redirect()->back()->withErrors($validator)->withInput();
  }

  }
public function listAttributeGroup()
{

  $pageHeading= "attribute_group";
  $pageTitle = "List Attribute Group";
  $attributegroups = Mst_attribute_group::all();

  return view('store.elements.attribute_group.list',compact('attributegroups','pageTitle','pageHeading'));
}



  public function editAttributeGroup(Request $request, $id)
  {

  $decryptId = Crypt::decryptString($id);


  $pageTitle = "Edit Attribute Group";
    $attributegroup = Mst_attribute_group::Find($decryptId);

    return view('store.elements.attribute_group.edit',compact('attributegroup','pageTitle'));



      }

     public function updateAtrGroup(Request $request,
                   Mst_attribute_group $attributegroup, $attr_group_id)
    {

      $GrpId = $request->attr_group_id;
      $attributegroup = Mst_attribute_group::Find($GrpId);

    $validator = Validator::make($request->all(),
    [
      'group_name'   => 'required',

         ],
    [
        'group_name.required'        => 'Group name required',


    ]);

        if(!$validator->fails())
    {
      $data= $request->except('_token');

    $attributegroup->group_name  = $request->group_name;


    $attributegroup->update();

         return redirect('store/attribute_group/list')->with('status','Attribute group updated successfully.');
  }else
  {

    return redirect()->back()->withErrors($validator)->withInput();
    }
  }

  public function listAttr_Value()
  {

    $pageTitle = "List Attribute Value";
    $attributevalues = Mst_attribute_value::all();
    $attributegroups = Mst_attribute_group::all();

    return view('store.elements.attribute_value.list',compact('attributevalues','pageTitle','attributegroups'));


      }

 public function createAttr_Value(Request $request,Mst_attribute_value $attribute_value)
  {


    $pageTitle = "Create Attribute Value";
    $attributevalues = Mst_attribute_value::all();
    $attributegroups = Mst_attribute_group::all();

    //$attr_grps    = $request->$attribute_group_id;
    return view('store.elements.attribute_value.create',compact('attributevalues','pageTitle','attributegroups'));


      }

  public function storeAttr_Value(Request $request, Mst_attribute_value $attribute_value)
    {

      $validator = Validator::make($request->all(),
      [
          'group_value'       => 'required',
          'attribute_group_id' =>'required',

          ],
      [
          'group_value.required'          => 'Attribute value required',
          'attribute_group_id.required|nimeric' => 'Select group of attribute'


      ]);
      // $this->uploads($request);
        if(!$validator->fails())
          {
            $data= $request->except('_token');

          $values = $request->group_value;

            //dd($values);
            $attr_grp_value = $request->attribute_group_id;
            $Hexvalue = $request->Hexvalue;
            $group_value = $request->group_value;
            $status = 1;
            $date =  Carbon::now();
            // dd($date);
            if($attr_grp_value == 2)
            {
              if($Hexvalue)
              {
                $count = count($Hexvalue);
                //dd($count);

                //$countvalue = 2;
                for($i=0;$i<$count;$i++){

                $attribute_value = new Mst_attribute_value;
                $attribute_value->attribute_group_id = $attr_grp_value;
                $attribute_value->attr_value_status = $status;
                $attribute_value->group_value = $request->group_value[$i];
                $attribute_value->Hexvalue = $Hexvalue[$i];
                $attribute_value->created_at = $date;
                $attribute_value->updated_at = $date;

                $attribute_value->save();

            }
          }


      }else{

          foreach($values as $value){

          $data = [[
             'group_value'=>$value,
             'attribute_group_id'=>$request->attribute_group_id,
             'attr_value_status'=>1,
             'created_at'=> $date,
           'updated_at'=> $date,


          ],
            ];
        //dd($data);

    Mst_attribute_value::insert($data);
          }

      }

        return redirect('store/attribute_value/list')->with('status','Attribute added successfully.');
  }else
  {
    //return redirect('/')->withErrors($validator->errors());
    return redirect()->back()->withErrors($validator)->withInput();
  }

  }
  public function editAttr_Value(Request $request, $id)
  {

  $decryptId = Crypt::decryptString($id);

  $pageTitle = "Edit Attribute Value";
    $attributevalue = Mst_attribute_value::Find($decryptId);
    $attributegroups = Mst_attribute_group::all();

    return view('store.elements.attribute_value.edit',compact('attributevalue','attributegroups','pageTitle'));


      }

  public function updateAttr_Value(Request $request,
                   Mst_attribute_value $attributevalue, $attr_value_id)
    {

      $GrpId = $request->attr_value_id;
      $attributevalue = Mst_attribute_value::Find($GrpId);

  $validator = Validator::make($request->all(),
    [
      'group_value'   => 'required',
      'attribute_group_id' =>'required',

         ],
    [
        'group_value.required'        => 'Group value required',
        'attribute_group_id'          => 'Group name required'


    ]);
      // $this->uploads($request);
        if(!$validator->fails())
    {
      $data= $request->except('_token');

    $attributevalue->group_value  = $request->group_value;
    $attributevalue->attribute_group_id  = $request->attribute_group_id;
    if($request->attribute_group_id == 2)
    {
      $attributevalue->Hexvalue  = $request->Hexvalue;
    }

    $attributevalue->update();
  //dd($fetch);
         return redirect('store/attribute_value/list')->with('status','Attribute value updated successfully.');
  }else
  {

    return redirect()->back()->withErrors($validator)->withInput();
  }
}
public function destroyAttr_Value(Request $request,Mst_attribute_value $attribute_value)
  {

      $delete = $attribute_value->delete();


    return redirect('store/attribute_value/list')->with('status','Attribute value deleted successfully.');;
  }
public function destroyAttr_Group(Request $request,Mst_attribute_group $attribute_group)
  {

      $delete = $attribute_group->delete();


    return redirect('store/attribute_group/list')->with('status','Attribute group deleted successfully.');;
  }

// inventory management


    public function listInventory(Request $request)
    {
        $pageTitle = "Inventory Management";
        $store_id =  Auth::guard('store')->user()->store_id;
     
     $products = Mst_store_product_varient::
        join('mst_store_products','mst_store_products.product_id','=','mst_store_product_varients.product_id')
                                        ->join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')

        ->where('mst_store_products.store_id',$store_id)
            ->where('mst_store_products.product_type',1)

        ->orderBy('mst_store_product_varients.stock_count','ASC')
        ->select(
          'mst_store_products.product_id',
          'mst_store_products.product_name',
          'mst_store_products.product_code',
          'mst_store_products.product_cat_id',
          'mst_store_products.product_base_image',
          'mst_store_products.product_status',
          'mst_store_products.product_brand',
          'mst_store_product_varients.product_varient_id',
          'mst_store_product_varients.variant_name',
          'mst_store_product_varients.product_varient_price',
          'mst_store_product_varients.product_varient_offer_price',
          'mst_store_product_varients.product_varient_base_image',
          'mst_store_product_varients.stock_count'
        )
        ->paginate(10);
        $category = Mst_categories::where('category_status',1)->get();

      if($_GET){
        
        $query = Mst_store_product_varient::
        join('mst_store_products','mst_store_products.product_id','=','mst_store_product_varients.product_id')
                                                ->join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')

        ->where('mst_store_products.store_id',$store_id)
        ->orderBy('mst_store_product_varients.stock_count','ASC')
            ->where('mst_store_products.product_type',1)

        ->select(
          'mst_store_products.product_id',
          'mst_store_products.product_name',
          'mst_store_products.product_code',
          'mst_store_products.product_cat_id',
          'mst_store_products.product_base_image',
          'mst_store_products.product_status',
          'mst_store_products.product_brand',
          'mst_store_product_varients.product_varient_id',
          'mst_store_product_varients.variant_name',
          'mst_store_product_varients.product_varient_price',
          'mst_store_product_varients.product_varient_offer_price',
          'mst_store_product_varients.product_varient_base_image',
          'mst_store_product_varients.stock_count'
        );
        
        if($request->product_cat_id){
          $query = $query->where('product_cat_id',$request->product_cat_id);
        }
        $products = $query->paginate(10);
        return view('store.elements.inventory.list',compact('category','products','pageTitle'));
      }

      return view('store.elements.inventory.list',compact('category','products','pageTitle'));

    }

  

    public function UpdateStock(Request $request)
    {

        $updated_stock = $request->updated_stock;
        $product_varient_id = $request->product_varient_id;
        
    $usOld = DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->first();
    
        if( $us = DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->increment('stock_count',$updated_stock))
        {
            $usData = DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->first();
            $usProData =  DB::table('mst_store_products')->where('product_id', $usData->product_id)->first();
              
               $sd = new Mst_StockDetail;
               $sd->store_id = $usProData->store_id;
               $sd->product_id = $usData->product_id;
               $sd->stock = $request->updated_stock;
               $sd->product_varient_id = $request->product_varient_id;
               $sd->prev_stock = $usOld->stock_count;
            
               $sd->save();
            
            $s = DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->pluck("stock_count");

            return response()->json($s);

        }else{
            echo "error";
        }

    }

    public function resetStock(Request $request)
    {

        $product_varient_id = $request->product_varient_id;

        if( $us = DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->update(['stock_count' => 0]))
        {
            $s = DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->pluck("stock_count");

            return response()->json($s);

        }else{
            echo "error";
        }

    }

 public function listPOS()
    {
      $pageTitle = "POS";
    	$store_id =   Auth::guard('store')->user()->store_id;

     $customer = Trn_store_customer::all();        
   //  $products = Mst_store_product::where('store_id',$store_id)->where('stock_count','!=',0)->get();
     $tax = Mst_Tax::all();

     $products = Mst_store_product::
     join('mst_store_product_varients','mst_store_product_varients.product_id','=','mst_store_products.product_id')
     ->where('mst_store_products.store_id',$store_id)
     ->where('mst_store_products.product_status',1)
     ->where('mst_store_product_varients.stock_count','>',0)
     ->orderBy('mst_store_products.product_id','DESC')
     ->get();

   return view('store.elements.pos.list',compact('tax','products','customer','pageTitle'));
      
    }
    
     public function checkProductAvailability(Request $request)
    {

        $product_id = $request->product_id;
        $product_varient_id = $request->product_varient_id;
        $quantity = $request->quantity;
        $varData = Mst_store_product_varient::find($product_varient_id);
        if($varData->stock_count >= $quantity)
        {
            echo "available";
        }
        else
        {
            echo $varData->stock_count;
        }
    }

    
    public function findProduct(Request $request)
    {

        $product_id = $request->product_id;
        $product_varient_id = $request->product_varient_id;

        $products = DB::table('mst_store_products')->where('product_id', $product_id)->first();
        $products = Mst_store_product::
          join('mst_store_product_varients','mst_store_product_varients.product_id','=','mst_store_products.product_id')
          ->where('mst_store_products.product_id','=',$product_id)
          ->where('mst_store_product_varients.product_varient_id','=',$product_varient_id)
          ->select('mst_store_product_varients.product_varient_offer_price',
            'mst_store_product_varients.product_varient_id',
            'mst_store_product_varients.stock_count',
            'mst_store_product_varients.product_varient_price',
            'mst_store_products.*'
          )
          ->first();

        $tax = Mst_Tax::find($products->tax_id);
        if(isset($tax->tax_value))
        $products->tax =  $tax->tax_value;
        else
        $products->tax =  0;

       // dd($products);
        return response()->json($products);

    }

     public function findCustomer(Request $request)
    {

        $customer_id = $request->customer_id;

        $customer = DB::table('trn_store_customers')->where('customer_id', $customer_id)->first();
     
        return response()->json($customer);

    }

    public function findTax(Request $request)
    {

        $product_id = $request->product_id;

        $products = DB::table('mst_store_products')->where('product_id', $product_id)->first();
     
        return response()->json($products);

    }

   public function savePOS(Request $request,Trn_store_order $store_order,Trn_store_order_item $order_item)
    {
    try{  
       
       $storeOrderCount = Trn_store_order::where('store_id',Auth::guard('store')->user()->store_id)->count();

        $orderNumber = @$storeOrderCount + 1;
        
        $storeData = Mst_store::where('store_id',Auth::guard('store')->user()->store_id)->select('order_number_prefix')->first();
        
        if(isset($storeData->order_number_prefix))
        {
            $orderNumberPrefix = $storeData->order_number_prefix;
        }
        else
        {
            $orderNumberPrefix = 'ORDRYSTR';
        }

        $store_order->order_number = $orderNumberPrefix.@$orderNumber;
            
        $store_order->customer_id = 3;
        $store_order->store_id =  Auth::guard('store')->user()->store_id;
        $store_order->subadmin_id =  Auth::guard('store')->user()->subadmin_id;
        $store_order->product_total_amount =  $request->get('full_amount');
        $store_order->payment_type_id = 1;
        $store_order->payment_status = 9;
        $store_order->status_id = 9;
        $store_order->order_type = 'POS';

        $store_order->save();
        $order_id = DB::getPdo()->lastInsertId();

        

        $invoice_info['order_id'] = $order_id;
        $invoice_info['invoice_date'] =  Carbon::now()->format('Y-m-d');
        $invoice_info['invoice_id'] = "INV0".$order_id;
        
        Trn_order_invoice::insert($invoice_info);

       // dd($data);
        $quantity = $request->get('quantity');
        $single_quantity_rate = $request->get('single_quantity_rate');
        $discount_amount = $request->get('discount_amount');
        $discount_percentage = $request->get('discount_percentage');
        $total_tax = $request->get('total_tax');
        $total_amount = $request->get('total_amount');
        $pro_variant = $request->get('product_varient_id');
        
        $i = 0;

        foreach ($request->get('product_id') as $p_id) {
        //  echo "here";

          $product_detail = Mst_store_product::where('product_id','=',$p_id)->get();

          $productVarOlddata =  Mst_store_product_varient::find($pro_variant[$i]);

          Mst_store_product_varient::where('product_varient_id','=',$pro_variant[$i])->decrement('stock_count',$quantity[$i]);

          if(!isset($discount_amount[$i]))
          {
            $discount_amount[$i] = 0;
          }
          
          
          $negStock = -1 * abs($quantity[$i]);

           $sd = new Mst_StockDetail;
           $sd->store_id = Auth::guard('store')->user()->store_id;
           $sd->product_id = $p_id;
           $sd->stock = $negStock;
           $sd->product_varient_id = $pro_variant[$i];
           $sd->prev_stock = $productVarOlddata->stock_count;
           $sd->save();

          $data = [
            'order_id'=> $order_id,
            'product_id'=> $p_id,
            'product_varient_id'=> $pro_variant[$i],
            'customer_id'=> $request->get('customer_id'),
            'store_id'=> Auth::guard('store')->user()->store_id,
            'quantity'=> $quantity[$i],
            'unit_price'=>  $single_quantity_rate[$i],
            'tax_amount'=> $total_tax[$i],
            'total_amount'=> $total_amount[$i],
            'discount_amount'=> $discount_amount[$i],
            'discount_percentage'=> $discount_percentage[$i],
            

        ];

          
          
          Trn_store_order_item::insert($data);

        //  $order_item->save();

          $i++;
        }
       // die;
        return  redirect()->back()->with('status','Order placed successfully.');
   } catch (\Exception $e) {return redirect()->back()->withErrors(['Something went wrong!'])->withInput();}
      }


      
      public function settings(Request $request)
      {
        $pageTitle = "Store Settings";
        $store_id =   Auth::guard('store')->user()->store_id;
        $store = Mst_store::find($store_id);
        $business_types = Mst_business_types::all();
        $districts = District::where('state_id',$store->store_state_id)->get();

        $store_settings = Trn_store_setting::where('store_id',Auth::guard('store')->user()->store_id)->get();
        $settingcount = Trn_store_setting::where('store_id',Auth::guard('store')->user()->store_id)->count();

    
        return view('store.elements.settings.create',compact('settingcount','store_settings','store','districts','pageTitle','business_types'));

    
        
      }

      public function updateStoreSettings(Request $request)
      {
      //  Trn_store_setting
      
      if(isset($request->start) < 1)
      {
        return redirect()->back();
      }

        $s_count = Trn_store_setting::where('store_id',Auth::guard('store')->user()->store_id)->count();
        
        if($s_count >= 1)
        {
           Trn_store_setting::where('store_id',Auth::guard('store')->user()->store_id)->delete();
        }

            $i = 0;
            $start = $request->start;
            $end = $request->end;
            $delivery_charge = $request->delivery_charge;
            $packing_charge = $request->packing_charge;

            $data = [
            
              'service_area'=> $request->service_area,
              'store_district_id'=>  $request->service_district,
              'town_id'=> $request->service_town,
              'business_type_id'=> $request->business_type_id,
              'order_number_prefix'=> $request->order_number_prefix,
              
              ];

            Mst_store::where('store_id',Auth::guard('store')->user()->store_id)->update($data);

  

            

            foreach($request->start as $s)
            {
              $info = [
                'store_id'=> Auth::guard('store')->user()->store_id,
                'service_start'=> $start[$i],
                'service_end'=>  $end[$i],
                'delivery_charge'=> $delivery_charge[$i],
                'packing_charge'=> $packing_charge[$i],
                
                 ];

              Trn_store_setting::insert($info);
              $i++;
            }

        return redirect()->back()->with('status','Store settings updated successfully.');

      }

      

      public function time_slot(Request $request)
      {
        $pageTitle = "Working Days";
        $store_id =   Auth::guard('store')->user()->store_id;
        $store = Mst_store::find($store_id);

  
        $time_slots_count = Trn_StoreTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->count();
        $time_slots = Trn_StoreTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->get();

  
        return view('store.elements.time_slot.create',compact('time_slots_count','time_slots','store','pageTitle','store_id'));
        
      }

      public function delivery_time_slots(Request $request)
      {
        $pageTitle = "Time Slots";
        $store_id =   Auth::guard('store')->user()->store_id;
        $store = Mst_store::find($store_id);

        $time_slots_count = Trn_StoreDeliveryTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->count();
        $time_slots = Trn_StoreDeliveryTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->get();

        return view('store.elements.time_slot.delivery_time_slot',compact('time_slots','time_slots_count','store','pageTitle','store_id'));
        
      }

      public function update_delivery_time_slots(Request $request)
      {
       // dd($request->all());
         
        try{  
         
        $start = $request->start;
        $end = $request->end;

        $s_count = Trn_StoreDeliveryTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->count();
        
        if($s_count >= 1)
        {
          Trn_StoreDeliveryTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->delete();
        }

        
        $i = 0;
        foreach($request->start as $s)
          {
            $info = [
              'store_id'=> Auth::guard('store')->user()->store_id,
              'time_start'=>  $start[$i],
              'time_end'=> $end[$i],
              ];

              //print_r($info);die;

              Trn_StoreDeliveryTimeSlot::insert($info);
            $i++;
          }
          return  redirect()->back()->with('status','Time slots updated successfully.');
        } catch (\Exception $e) { return redirect()->back()->withErrors(['Something went wrong!'])->withInput(); }
      }

      
      
      public function updateTimeSlot(Request $request)
      {
       // dd($request->all());
         
        $start = $request->start;
        $end = $request->end;
        $day = $request->day;

        $s_count = Trn_StoreTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->count();
        
        if($s_count > 1)
        {
          Trn_StoreTimeSlot::where('store_id',Auth::guard('store')->user()->store_id)->delete();
        }

        
        $i = 0;
        foreach($request->day as $s)
          {
            $info = [
              'store_id'=> Auth::guard('store')->user()->store_id,
              'day'=> $day[$i],
              'time_start'=>  $start[$i],
              'time_end'=> $end[$i],
              ];

              //print_r($info);die;

              Trn_StoreTimeSlot::insert($info);
            $i++;
          }
          return  redirect()->back()->with('status','Working days updated successfully.');

      }

// Store Admin

  public function listStoreAdmin()
  {
      $pageTitle = "List Store Admin";
      $store_admin = Trn_StoreAdmin::orderBy('store_admin_id', 'DESC')->where('role_id','!=',0)->where('store_id',Auth::guard('store')->user()->store_id)->get();
      return view('store.elements.store_admin.list',compact('store_admin','pageTitle'));
  }

  public function createStoreAdmin()
  {
      $pageTitle = "Create Store Admin";
      return view('store.elements.store_admin.create',compact('pageTitle'));
  }

  public function storeStoreAdmin(Request $request,Trn_StoreAdmin $store_admin)
  {

      $validator = Validator::make($request->all(),
      [
          'admin_name' => ['required'],
          'phone' => ['required','unique:trn__store_admins,store_mobile'],
          'username' => ['required','unique:trn__store_admins'],
          'password'  => 'required|min:5|same:password_confirmation',
          'role_id' => ['required'],

      ],
      [
        'admin_name.required'         => 'Admin name required',
        'phone.required'         => 'Phone required',
        'username.required'         => 'Username required',
        'phone.unique'         => 'Phone number exists',
        'username.unique'         => 'Username exists',
        'role_id.required'         => 'Role required',
        'password.required'         => 'Password required',
		    'password.confirmed'         => 'Passwords are not matching',
      ]);
      if(!$validator->fails())
      {
          $store_admin->store_id =  Auth::guard('store')->user()->store_id;
          $store_admin->admin_name = $request->admin_name;
          $store_admin->store_mobile = $request->phone;
          $store_admin->email = $request->email;
          $store_admin->password = Hash::make($request->password);
          $store_admin->username = $request->username;
          $store_admin->role_id = $request->role_id;
          $store_admin->store_account_status = $request->status;
          $store_admin->store_otp_verify_status = 1;
          $store_admin->save();

          return redirect('store/admin/list')->with('status','Store admin added successfully.');
      }
      else
      {
          return redirect()->back()->withErrors($validator)->withInput();
      }

  }

  public function editStoreAdmin($store_admin_id)
  {
      $store_admin_id  = Crypt::decryptString($store_admin_id);
      $pageTitle = "Edit store_admin";
      $store_admin = Trn_StoreAdmin::Find($store_admin_id);
      return view('store.elements.store_admin.edit',compact('store_admin','store_admin_id','pageTitle'));
  }

  public function updateStoreAdmin(Request $request, $store_admin_id)
  {
    try{  



     // $store_a = Trn_StoreAdmin::Find($store_admin_id);

   //  echo  $password = $store_a->password; 
   //   echo $newpassword = $request->password; die;

      $validator = Validator::make($request->all(),
      [
          'admin_name' => ['required'],
          'phone' => ['required'],
          'username' => ['required'],
          //'password' => 'sometimes|same:password_confirmation',
          'role_id' => ['required'],

      ],
      [
        'admin_name.required'         => 'Admin name required',
        'phone.required'         => 'Phone required',
        'username.required'         => 'Username required',
        'role_id.required'         => 'Role required',
		 //   'password.confirmed'         => 'Passwords are not matching',
      ]);
      if(!$validator->fails())
      {
        //dd($request->all());

          $data['admin_name'] = $request->admin_name;
          $data['store_mobile'] = $request->phone;
          $data['email'] = $request->email;
          
       
            // if($newpassword == '')
            // {
            //   $data['password'] = $password;
            // }else
            // {
            //   $data['password'] =  Hash::make($request->password);
            // }
            

          $data['username'] = $request->username;
          $data['role_id'] = $request->role_id;
          $data['store_account_status'] = $request->status;
          $data['store_otp_verify_status'] = 1;

          \DB::table('trn__store_admins')->where('store_admin_id',$store_admin_id)->update($data);


          return redirect('store/admin/list')->with('status','Store admin updated successfully.');
      }
      else
      {
          return redirect()->back()->withErrors($validator)->withInput();
      }
    } catch (\Exception $e) {
      //echo $e->getMessage();die;
      return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
    }
  }

  public function removeStoreAdmin(Request $request,Trn_StoreAdmin $store_admin,$store_admin_id)
  {
    Trn_StoreAdmin::where('store_admin_id',$store_admin_id)->delete();

      return redirect()->back()->with('status','Store admin deleted successfully.');
  }
  
  
    public function listGlobalProducts(Request $request)
    {
      try {
              $pageTitle = "List Global Products";
              $store_id =  Auth::guard('store')->user()->store_id;
              $products_global_products_id = Mst_store_product::where('store_id',$store_id)
              ->where('global_product_id','!=', null)
              ->orderBy('product_id', 'DESC')
              ->pluck('global_product_id')
              ->toArray();
              $category = Mst_categories::where('category_status',1)->get();

              $global_product = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id)->orderBy('global_product_id','DESC')->get();
              //dd($global_product);

              if($_GET){ 
                $query = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id);
                if($request->product_cat_id){
                  $query = $query->where('product_cat_id',$request->product_cat_id);
                }
                $global_product = $query->orderBy('global_product_id','DESC')->get();
              }

              return view('store.elements.global_product.list',compact('category','global_product','pageTitle'));
        } catch (\Exception $e) { return redirect()->back()->withErrors(['Something went wrong!'])->withInput(); }
      }
      
    public function viewGlobalProduct(Request $request,$global_product_id)
    {
        $global_product_id  = Crypt::decryptString($global_product_id);
        $pageTitle = "View Global Product";
        $product = Mst_GlobalProducts::Find($global_product_id);
        $product_images = Trn_GlobalProductImage::where('global_product_id',$global_product_id)->get();
        

        return view('store.elements.global_product.view',compact('product_images','product','global_product_id','pageTitle'));
    }

    public function convertGlobalProducts(Request $request,Mst_store_product $product, $global_product_id)
    {
       try {
        $global_product = Mst_GlobalProducts::find($global_product_id);
       // dd($global_product);
        $store_id =  Auth::guard('store')->user()->store_id;


     // $product = Mst_store_product::where('store_id','=',$user_id)->get()->count();

     $product->product_name = $global_product->product_name;
     $product->product_name_slug = Str::of($global_product->product_name)->slug('-');
     $product->product_code = $global_product->product_code;
     if(isset($global_product->business_type_id))
     $product->business_type_id = $global_product->business_type_id;
     else
     $product->business_type_id = 0;

     if(isset($global_product->product_cat_id))
     $product->product_cat_id = $global_product->product_cat_id;
     else
     $product->product_cat_id = 0;
     
      if(isset($global_product->sub_category_id))
     $product->sub_category_id = $global_product->sub_category_id;
     else
     $product->sub_category_id = 0;

     if(isset($global_product->regular_price))
     $product->product_price = $global_product->regular_price;
     else
     $product->product_price = 0;

     if(isset($global_product->sale_price))
     $product->product_price_offer = $global_product->sale_price;
     else
     $product->product_price_offer = 0;

     if(isset($global_product->attr_group_id))
     $product->attr_group_id = $global_product->attr_group_id;
     else
     $product->attr_group_id = 0;

     if(isset($global_product->attr_value_id))
     $product->attr_value_id = $global_product->attr_value_id;
     else
     $product->attr_value_id = 0;

     if(isset($global_product->tax_id))
     $product->tax_id = $global_product->tax_id;
     else
     $product->tax_id = 0;

    
     if(isset($global_product->color_id))
     $product->color_id = $global_product->color_id;
     else
     $product->color_id = 0;

     if(isset($global_product->vendor_id))
     $product->vendor_id = $global_product->vendor_id;
     else
     $product->vendor_id = 0;


     //$product->product_cat_id = $global_product->product_cat_id;
    // $product->product_price = $global_product->regular_price;
    // $product->product_price_offer = $global_product->sale_price;
     $product->product_description = $global_product->product_description;
     $product->product_base_image = $global_product->product_base_image;
     $product->store_id = $store_id;
     $product->product_brand = $global_product->product_brand;
          //$product->attr_group_id = $global_product->attr_group_id;
    // $product->attr_value_id = $global_product->attr_value_id;
     $product->stock_count = $global_product->min_stock;
     //$product->tax_id = $global_product->tax_id;
    // $product->color_id = $global_product->color_id;
    // $product->vendor_id = $global_product->vendor_id;
     $product->global_product_id = $global_product->global_product_id;

     $product->product_status = 1;
     $product->product_type = 1;
     $product->draft = 1;

     $product->save();
     $id = DB::getPdo()->lastInsertId();
     
     
      
     
            $data3 = [
                'product_id' => $id,
                'store_id' => $store_id,
                'variant_name' => $global_product->product_name,
                'product_varient_price' => $global_product->regular_price,
                'product_varient_offer_price' => $global_product->sale_price,
                'product_varient_base_image' => $global_product->product_base_image,
                'stock_count' => 0,
                'color_id' =>  0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
              ];

              Mst_store_product_varient::create($data3);
              $vari_id = DB::getPdo()->lastInsertId();
              
              

     $global_product_images = Trn_GlobalProductImage::where('global_product_id',$global_product_id)->get();
     
      foreach ($global_product_images as $file) {

         $date = Carbon::now();
          $data1= [[
                  'product_image'      => $file->image_name,
                  'product_id' => $id,
                  'product_varient_id' => 0,
                  'image_flag'         => 0,
                  'created_at'         => $date,
                  'updated_at'         => $date,
                      ],
                    ];

               Mst_product_image::insert($data1);
               
                $proImg_Id = DB::getPdo()->lastInsertId();
                
                if($global_product->product_base_image == $file->image_name)
                {
                    DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                }

        }
        
        // global product videos
        $global_product_videos = Trn_GlobalProductVideo::where('global_product_id',$global_product_id)->get();
       
        foreach ($global_product_videos as $vid) {

            $pv = new Trn_ProductVideo;
            $pv->product_id = $id;
            $pv->product_varient_id = 0;
            $pv->link = $vid->video_code;
            $pv->platform = $vid->platform;
            $pv->is_active = 1;
            $pv->save();
           
        }

     
     
     
     
     foreach ($global_product_images as $file) {

         $date = Carbon::now();
          $data1= [[
                  'product_image'      => $file->image_name,
                  'product_id' => $id,
                  'product_varient_id' => $vari_id,
                  'image_flag'         => 0,
                  'created_at'         => $date,
                  'updated_at'         => $date,
                      ],
                    ];

               Mst_product_image::insert($data1);
               
                $proImg_Id = DB::getPdo()->lastInsertId();
                
                if($global_product->product_base_image == $file->image_name)
                {
                    DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                }

        }



     return redirect('/store/global/products/list')->with('status','Global product added to store successfully');
        
      } catch (\Exception $e) { return redirect()->back()->withErrors(['Something went wrong!'])->withInput(); }

    }
    
    
    public function storeToGlobalProducts(Request $request )
    {
      

       try {

        foreach($request->global_product_idz as $global_product_id)
        {
      
        

                  $global_product = Mst_GlobalProducts::find($global_product_id);
                // dd($global_product);
                  $store_id =  Auth::guard('store')->user()->store_id;


              // $product = Mst_store_product::where('store_id','=',$user_id)->get()->count();

              $product['product_name'] = $global_product->product_name;
              $product['product_name_slug'] = Str::of($global_product->product_name)->slug('-');
              $product['product_code'] = $global_product->product_code;
              if(isset($global_product->business_type_id))
              $product['business_type_id'] = $global_product->business_type_id;
              else
              $product['business_type_id'] = 0;

              if(isset($global_product->product_cat_id))
              $product['product_cat_id'] = $global_product->product_cat_id;
              else
              $product['product_cat_id'] = 0;
              
              if(isset($global_product->sub_category_id))
               $product['sub_category_id'] =  $global_product->sub_category_id;
                 else
               $product['sub_category_id'] =  0;

              if(isset($global_product->regular_price))
              $product['product_price'] = $global_product->regular_price;
              else
              $product['product_price'] = 0;

              if(isset($global_product->sale_price))
              $product['product_price_offer'] = $global_product->sale_price;
              else
              $product['product_price_offer'] = 0;

              if(isset($global_product->attr_group_id))
              $product['attr_group_id'] = $global_product->attr_group_id;
              else
              $product['attr_group_id'] = 0;

              if(isset($global_product->attr_value_id))
              $product['attr_value_id'] = $global_product->attr_value_id;
              else
              $product['attr_value_id'] = 0;

              if(isset($global_product->tax_id))
              $product['tax_id'] = $global_product->tax_id;
              else
              $product['tax_id'] = 0;

    
              if(isset($global_product->color_id))
              $product['color_id'] = $global_product->color_id;
              else
              $product['color_id'] = 0;

              if(isset($global_product->vendor_id))
              $product['vendor_id'] = $global_product->vendor_id;
              else
              $product['vendor_id'] = 0;


              //$product->product_cat_id = $global_product->product_cat_id;
              // $product->product_price = $global_product->regular_price;
              // $product->product_price_offer = $global_product->sale_price;
              $product['product_description'] = $global_product->product_description;
              $product['product_base_image'] = $global_product->product_base_image;
              $product['store_id'] = $store_id;
              //$product->attr_group_id = $global_product->attr_group_id;
              // $product->attr_value_id = $global_product->attr_value_id;
              $product['stock_count'] = $global_product->min_stock;
              //$product->tax_id = $global_product->tax_id;
              // $product->color_id = $global_product->color_id;
              // $product->vendor_id = $global_product->vendor_id;
              $product['global_product_id'] = $global_product_id;
              $product['product_brand'] = $global_product->product_brand;;
              
              $product['product_status'] = 1;
              $product['product_type'] = 1;
              $product['draft'] = 1;
              
             // dd($product);

              Mst_store_product::create($product);
              $id = DB::getPdo()->lastInsertId();
              
               $data3 = [
                'product_id' => $id,
                'store_id' => $store_id,
                'variant_name' => $global_product->product_name,
                'product_varient_price' => $global_product->regular_price,
                'product_varient_offer_price' => $global_product->sale_price,
                'product_varient_base_image' => $global_product->product_base_image,
                'stock_count' => 0,
                'color_id' =>  0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
              ];

              Mst_store_product_varient::create($data3);
              $vari_id = DB::getPdo()->lastInsertId();

            $global_product_images = Trn_GlobalProductImage::where('global_product_id',$global_product_id)->get();

            foreach ($global_product_images as $file) {

                $date = Carbon::now();
                  $data1= [[
                          'product_image'      => $file->image_name,
                          'product_id' => $id,
                          'product_varient_id' => 0,
                          'image_flag'         => 0,
                          'created_at'         => $date,
                          'updated_at'         => $date,
                              ],
                            ];

                      Mst_product_image::insert($data1);
                        $proImg_Id = DB::getPdo()->lastInsertId();
                
                        if($global_product->product_base_image == $file->image_name)
                        {
                            DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                        }
                
                }
                
            // global product videos
            $global_product_videos = Trn_GlobalProductVideo::where('global_product_id',$global_product_id)->get();
           
            foreach ($global_product_videos as $vid) {
    
                $pv = new Trn_ProductVideo;
                $pv->product_id = $id;
                $pv->product_varient_id = 0;
                $pv->link = $vid->video_code;
                $pv->platform = $vid->platform;
                $pv->is_active = 1;
                $pv->save();
               
            }

                
                foreach ($global_product_images as $file) {

                $date = Carbon::now();
                  $data1= [[
                          'product_image'      => $file->image_name,
                          'product_id' => $id,
                          'product_varient_id' => $vari_id,
                          'image_flag'         => 0,
                          'created_at'         => $date,
                          'updated_at'         => $date,
                              ],
                            ];

                      Mst_product_image::insert($data1);
                        $proImg_Id = DB::getPdo()->lastInsertId();
                
                        if($global_product->product_base_image == $file->image_name)
                        {
                            DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                        }
                
                }
                
                
                // if(isset($global_product->product_base_image)){
                //     $date = Carbon::now();
                //     $data2= [[
                //       'product_image'      => $global_product->product_base_image,
                //       'product_id' => $id,
                //       'product_varient_id' => $id,
                //       'image_flag'         => 1,
                //       'created_at'         => $date,
                //       'updated_at'         => $date,
                //           ],
                //         ];
                //         Mst_product_image::insert($data2);
                // }
                
                

        }
   


     return redirect()->back()->with('status','Global products added to store successfully');
        
      } catch (\Exception $e) { 
          // return redirect()->back()->withErrors([  $e->getMessage() ])->withInput();
        return redirect()->back()->withErrors(['Select product'])->withInput(); 
          
          
          
      }

    }


public function storePayments(Request $request)
    {
      $pageTitle = "Payments";
      $store_id  = Auth::guard('store')->user()->store_id;
      $payments_datas = \DB::table('trn_store_payments_tracker')->where('store_id',$store_id)->get();

        if($_GET){
   
           $year = $request->year;
           $month = $request->month;
           $a1 =Carbon:: parse($year.'-'.$month)->startOfMonth();
           $a2  = Carbon:: parse($year.'-'.$month)->endOfMonth();
   
          $store_payments = Trn_store_payment_settlment::where('store_id', $store_id)
          ->whereBetween('created_at',[@$a1,@$a2])->get();
   
                $payments = Trn_store_payment_settlment::whereBetween('created_at',[@$a1,@$a2])->get();
                $payments_datas = \DB::table('trn_store_payments_tracker')
                ->where('store_id',$store_id)
                ->whereBetween('date_of_payment',[@$a1,@$a2])
                ->get();

                return view('store.elements.payments.view',compact('store_id','payments_datas','payments','store_payments','pageTitle'));
   
   
     }
   
        return view('store.elements.payments.view',compact('payments_datas','store_id','pageTitle'));
   
    }
    
    public function destroyProductVariant(Request $request, $product_varient_id)
    {
           $pro_variant = Mst_store_product_varient::where('product_varient_id','=',$product_varient_id)->first();
          
           $pro_variant->delete();
           
            $productVarCount = Mst_store_product_varient::where('product_id',$pro_variant->product_id)->count();
            
           if($productVarCount < 1)
           {
               Mst_store_product::where('product_id',$pro_variant->product_id)->update(['product_status' => 0]);
           }
           
           return redirect()->back()->with('status','Product variant deleted successfully.');
    }

    public function destroyProductVariantAttr(Request $request, $variant_attribute_id)
    {
           $pro_variant_attr = Trn_ProductVariantAttribute::where('variant_attribute_id','=',$variant_attribute_id);
           $pro_variant_attr->delete();
           return redirect()->back()->with('status','Product variant attribute deleted successfully.');
    }

    public function addProductVariantAttr(Request $request,Trn_ProductVariantAttribute $var_att)
    {
        
      $validator = Validator::make($request->all(),
      [
          'attr_grp_id' => ['required'],
          'attr_val_id' => ['required'],
      ],
      [
        'attr_grp_id.required'         => 'Attribute group required',
        'attr_val_id.required'         => 'Attribute value required',
      ]);
      
      if(!$validator->fails())
      {
          
        $var_att->product_varient_id = $request->product_varient_id;
        $var_att->attr_group_id = $request->attr_grp_id;
        $var_att->attr_value_id = $request->attr_val_id;
        $var_att->save();
        return redirect()->back()->with('status','New attribute added to product variant successfully.');
      }
      else{
          return redirect()->back()->withErrors($validator)->withInput();

      }

    }

    public function listProductVariant(Request $request,$product_id)
    {
      $pageTitle = "Product Variants";
      $store_id  = Auth::guard('store')->user()->store_id;
      $attr_groups = Mst_attribute_group::all();
 
      $product_variants = Mst_store_product_varient::where('product_id','=',$product_id)->get();
      return view('store.elements.product.view_variants',compact('attr_groups','product_variants','pageTitle','store_id'));

    }
    
     public function editProductVariant(Request $request,$product_varient_id)
    {
      $pageTitle = "Edit Product Variant";
      $store_id  = Auth::guard('store')->user()->store_id;
      $attr_groups = Mst_attribute_group::all();
 
      $product_variant = Mst_store_product_varient::find($product_varient_id);
      return view('store.elements.product.edit_variant',compact('attr_groups','product_variant','pageTitle','store_id'));

    }

    public function updateProductVariant(Request $request,$product_varient_id)
    {    
      $data['variant_name'] = $request->variant_name;
      $data['product_varient_price'] = $request->product_varient_price;
      $data['product_varient_offer_price'] = $request->product_varient_offer_price;
      $data['stock_count'] = $request->stock_count;
      if($request->hasFile('base_image')){
        
        $file = $request->file('base_image');
       // $filename = $file->getClientOriginalName();
                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();

        $file->move('assets/uploads/products/base_product/base_image', $filename);
        $data['product_varient_base_image'] = $filename;
      }
       Mst_store_product_varient::where('product_varient_id',$product_varient_id)->update($data);

      return redirect('store/product/list')->with('status','Product variant updated successfully.');

    }
    public function ShareItems(Request $request)
    { 
      //($request->all());

      $order = Trn_store_order::where('order_id',$request->order_id)->first();
      
        $url = url('item/list/'.Crypt::encryptString($request->order_id));
        $msg = 'Order number '.$order->order_number.' items list.       '.$url;
        //$msg = htmlentities($msg);

        return redirect()->away('https://api.whatsapp.com/send?phone=+91'.$request->mobile_number.'&text='.$msg);   
    }


    public function listDisputes(Request $request)
    {
      $pageTitle = "Disputes";
      $store_id  = Auth::guard('store')->user()->store_id;
          if($_GET){
  
            $datefrom = $request->date_from;
          $dateto = $request->date_to;
  
              $a1 =Carbon:: parse($request->date_from)->startOfDay();
              $a2  = Carbon:: parse($request->date_to)->endOfDay();
  
              $order_number  = $request->order_number;
  
      $query = \DB::table("mst_disputes")->where('store_id',$store_id)->select("*");
  
         
          if(isset($order_number)){
              $query = $query->where('order_number',$order_number);
          }
  
          if(isset($request->date_from) && isset($request->date_to) ){
              $query = $query->whereBetween('dispute_date',[$a1,$a2]);
          }
          if(isset($request->date_from))
          {
            $query = $query->whereDate('dispute_date',$request->date_from );
           

          }
          $query->orderBy('dispute_id','DESC');
          $disputes = $query->get();
            return view('store.elements.disputes.list',compact('dateto','datefrom','disputes','pageTitle'));
            }
        
        $disputes = \DB::table("mst_disputes")->where('store_id',$store_id)->select("*")->orderBy('dispute_id','DESC')->get();
        return view('store.elements.disputes.list',compact('disputes','pageTitle'));
  }

    public function statusDisputes(Request $request,$dispute_id)
    {
        $data['dispute_status']  = $request->dispute_status;
		    $query = \DB::table("mst_disputes")->where('dispute_id',$dispute_id)->update($data);
        return redirect()->back()->with('status','Status updated successfully.');

    }
    public function currentIssues(Request $request)
    {
      $pageTitle = "Current Disputes";
      $store_id  = Auth::guard('store')->user()->store_id;
      $disputes = \DB::table("mst_disputes")->where('store_id',$store_id)->select("*")
      ->orderBy('dispute_id','DESC')->get();
      return view('store.elements.disputes.list',compact('disputes','pageTitle'));
 
    }
    
    
     public function newIssues(Request $request)
    {
      $pageTitle = "New Disputes";
      $store_id  = Auth::guard('store')->user()->store_id;
      $disputes = \DB::table("mst_disputes")->where('store_id',$store_id)->select("*")
      ->whereDate('created_at', Carbon::today())->orderBy('dispute_id','DESC')->get();
      return view('store.elements.disputes.list',compact('disputes','pageTitle'));
 
    }
    
    public function showLocation(Request $request , $delivery_boy_id)
    {
        // echo "working";die;
        $pageTitle = 'Delivery Boy Location';
        $lastLoc = Trn_DeliveryBoyLocation::where('delivery_boy_id',$delivery_boy_id)->orderBy('dbl_id')->first();
        return view('store.elements.delivery_boys.location',compact('pageTitle','lastLoc'));

    }

    public function listDeliveryBoys(Request $request)
    {
      try {

        $pageTitle = "Delivery";
        $store_id  = Auth::guard('store')->user()->store_id;
        $delivery_boys = Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
        ->select('mst_delivery_boys.town_id','mst_delivery_boys.delivery_boy_id','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_mobile')
        ->where('mst_store_link_delivery_boys.store_id',$store_id)->get();

        $assigned_delivery_boys = [];
        $inprogress_delivery_boys = [];
        $completed_delivery_boys = [];

        $delivery_boys1 = Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
        // ->select('mst_delivery_boys.town_id','mst_delivery_boys.delivery_boy_id','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_mobile')
        ->where('mst_store_link_delivery_boys.store_id',$store_id)
        ->pluck('mst_delivery_boys.delivery_boy_id')
        ->toArray();

        $assigned_delivery_boys = Trn_store_order::whereIn('delivery_boy_id',$delivery_boys1)
        ->where('store_id',$store_id)
        ->where('status_id',7)
        ->where('delivery_status_id',1)
        ->orderBy('order_id','DESC')
        ->get();

        foreach($assigned_delivery_boys as $ab){
          $custData = Trn_store_customer::find(@$ab->customer_id);
          $ab->customer = @$custData->customer_first_name." ".@$custData->customer_last_name;
          
          
          $deliveryBoy = \DB::table('mst_delivery_boys')
          ->select('town_id','delivery_boy_id','delivery_boy_name','delivery_boy_mobile')
          ->where('delivery_boy_id',@$ab->delivery_boy_id)
          ->first();

          $ab->town_id = @$deliveryBoy->town_id;
          $ab->delivery_boy_id = @$deliveryBoy->delivery_boy_id;
          $ab->delivery_boy_name = @$deliveryBoy->delivery_boy_name;
          $ab->delivery_boy_mobile = @$deliveryBoy->delivery_boy_mobile;


        }


        $inprogress_delivery_boys = Trn_store_order::whereIn('delivery_boy_id',$delivery_boys1)
        ->where('store_id',$store_id)
        ->where('status_id',8)
        ->where('delivery_status_id',2)
        ->orderBy('order_id','DESC')
        ->get();

        foreach($inprogress_delivery_boys as $ab){
          
          $custData = Trn_store_customer::find(@$ab->customer_id);
          $ab->customer = @$custData->customer_first_name." ".@$custData->customer_last_name;
          
          $deliveryBoy = \DB::table('mst_delivery_boys')
          ->select('town_id','delivery_boy_id','delivery_boy_name','delivery_boy_mobile')
          ->where('delivery_boy_id',@$ab->delivery_boy_id)
          ->first();
          $ab->town_id = @$deliveryBoy->town_id;
          $ab->delivery_boy_id = @$deliveryBoy->delivery_boy_id;
          $ab->delivery_boy_name = @$deliveryBoy->delivery_boy_name;
          $ab->delivery_boy_mobile = @$deliveryBoy->delivery_boy_mobile;

        }


        $completed_delivery_boys = Trn_store_order::whereIn('delivery_boy_id',$delivery_boys1)
        ->where('store_id',$store_id)
        ->where('status_id',9)
        ->where('delivery_status_id',3)
        ->orderBy('order_id','DESC')
        ->get();

        foreach($completed_delivery_boys as $ab){
          
          $custData = Trn_store_customer::find(@$ab->customer_id);
          $ab->customer = @$custData->customer_first_name." ".@$custData->customer_last_name;
          
          $deliveryBoy = \DB::table('mst_delivery_boys')
          ->select('town_id','delivery_boy_id','delivery_boy_name','delivery_boy_mobile')
          ->where('delivery_boy_id',@$ab->delivery_boy_id)
          ->first();
          $ab->town_id = @$deliveryBoy->town_id;
          $ab->delivery_boy_id = @$deliveryBoy->delivery_boy_id;
          $ab->delivery_boy_name = @$deliveryBoy->delivery_boy_name;
          $ab->delivery_boy_mobile = @$deliveryBoy->delivery_boy_mobile;

        }



        //dd($assigned_delivery_boys);
        
        //   $delivery_boys1 = Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
        // ->select('mst_delivery_boys.town_id','mst_delivery_boys.delivery_boy_id','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_mobile')
        // ->where('mst_store_link_delivery_boys.store_id',$store_id)->get();


       // $assigned_delivery_boys = Trn_store_order::whereIn()

        // foreach($delivery_boys1 as $value)
        // {
        //   if($orderData = Trn_store_order::
        //   where('delivery_boy_id',$value->delivery_boy_id)
        //  // ->where('payment_type_id',2)
        //   ->where('delivery_status_id',1)
        //   ->orderBy('delivery_boy_id','DESC')->first())
        //   {
        //     $custData = Trn_store_customer::find(@$orderData->customer_id);
        //     $value->order_id = @$orderData->order_id;
        //       $value->order_number = @$orderData->order_number;
        //     $value->order_date = Carbon::parse(@$orderData->created_at)->format('d-m-Y');
        //     $value->customer = @$custData->customer_first_name." ".@$custData->customer_last_name;
        //     $assigned_delivery_boys[] = $value;
        //   }
        // }


        
        //   $delivery_boys2 = Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
        // ->select('mst_delivery_boys.town_id','mst_delivery_boys.delivery_boy_id','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_mobile')
        // ->where('mst_store_link_delivery_boys.store_id',$store_id)->get();

        // foreach($delivery_boys2 as $value)
        // {
        //   if($orderData = Trn_store_order::
        //   where('delivery_boy_id',$value->delivery_boy_id)
        // //  ->where('payment_type_id',2)
        //   ->where('delivery_status_id',2)
        //   ->orderBy('delivery_boy_id','DESC')->first())
        //   {
        //     $custData = Trn_store_customer::find(@$orderData->customer_id);
        //     $value->order_id = @$orderData->order_id;
        //       $value->order_number = @$orderData->order_number;
        //     $value->order_date = Carbon::parse(@$orderData->created_at)->format('d-m-Y');
        //     $value->customer = @$custData->customer_first_name." ".@$custData->customer_last_name;
        //     $inprogress_delivery_boys[] = $value;
        //   }
        // }
        
        //   $delivery_boys3 = Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
        // ->select('mst_delivery_boys.town_id','mst_delivery_boys.delivery_boy_id','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_name','mst_delivery_boys.delivery_boy_mobile')
        // ->where('mst_store_link_delivery_boys.store_id',$store_id)->get();

        // foreach($delivery_boys3 as $value)
        // {
        //   if($orderData = Trn_store_order::
        //   where('delivery_boy_id',$value->delivery_boy_id)
        // //  ->where('payment_type_id',2)
        //   ->where('delivery_status_id',3)
        //   ->orderBy('delivery_boy_id','DESC')->first())
        //   {
        //     $custData = Trn_store_customer::find(@$orderData->customer_id);
        //     $value->order_id = @$orderData->order_id;
        //       $value->order_number = @$orderData->order_number;
        //     $value->order_date = Carbon::parse(@$orderData->created_at)->format('d-m-Y');
        //     $value->customer = @$custData->customer_first_name." ".@$custData->customer_last_name;
        //     $completed_delivery_boys[] = $value;
        //   }
        // }

        return view('store.elements.delivery_boys.list',compact('completed_delivery_boys','inprogress_delivery_boys','assigned_delivery_boys','delivery_boys','pageTitle'));
       
      } catch (\Exception $e) { return redirect()->back()->withErrors(['Something went wrong!'])->withInput(); }
    }
    
    
     public function viewDeliveryOrder(Request $request,$id)
    {
      try{ 
        $pageTitle = "View Order";
        $decrId  = Crypt::decryptString($id);
        $order = Trn_store_order::Find($decrId);
        $order_items = Trn_store_order_item::where('order_id',$decrId)->get();

        $product = $order->product_id;

        $subadmin_id = Auth()->guard('store')->user()->subadmin_id;


        // $delivery_boys = \DB::table('mst_delivery_boys')
        //   ->where('subadmin_id',$subadmin_id)
        //   ->get();
          
           $delivery_boys = Mst_store_link_delivery_boy::join('mst_delivery_boys','mst_delivery_boys.delivery_boy_id','=','mst_store_link_delivery_boys.delivery_boy_id')
        ->select('mst_delivery_boys.*')
        ->where('mst_store_link_delivery_boys.store_id',$order->store_id)->get();
          

        $customer = Trn_store_customer::all();
        $status = Sys_store_order_status::all();

        return view('store.elements.delivery_boys.view_order',compact('delivery_boys','order_items','order','pageTitle','status','customer'));
        
        } catch (\Exception $e) {
          
            return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
      
        }
    }

    

    public function switchStatus(Request $request)
    {
        try{
           
                   $store_id  = Auth::guard('store')->user()->store_id;

                $storeData = Mst_store::find($store_id);
               //dd($storeData);
                if($storeData->online_status == 1)
                {
                    $online_status = 0;
                }
                else
                {
                    $online_status = 1;
                }
               
                if(Mst_store::where('store_id',$store_id)->update(['online_status'=> @$online_status]))
                {
                    return redirect()->back();
                }
                else
                {
                    return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
                }

         }catch (\Exception $e) {
             return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
         }catch (\Throwable $e) {
          return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
         } 
    }
    
    
    
     public function videoGallery(Request $request)
    {
        try{
            
            $pageTitle = 'Videos';
            
            // echo "Working page..";die;
           
             $videos = Mst_Video::where('status',1)->where('visibility',1)->orderBy('video_id','DESC')->get();
             //dd($videos);

            // foreach( $videos as $v)
            // {
            //     $linkCode = ' ';
            //     if($v->platform == 'Youtube')
            //     {
            //         $revLink = strrev($v->video_code);
            //         $revLinkCode = substr($revLink, 0, strpos($revLink, '='));
            //         $linkCode = strrev($revLinkCode);
            //       //  echo $revLink." *** ".$linkCode." *** ".$linkCode." *** ".$v->video_code;die;
            //     }
            //     if($v->platform == 'Vimeo')
            //     {
            //         $revLink = strrev($v->video_code);
            //         $revLinkCode = substr($revLink, 0, strpos($revLink, '/'));
            //         $linkCode = strrev($revLinkCode);

            //       //  echo $revLink." *** ".$linkCode." *** ".$linkCode." *** ".$v->video_code;die;
            //     }
            //     $v->link_code = @$linkCode;
            //     if($v->video_image)
            //     {
            //         $v->video_image = '/assets/uploads/video_images/'.$v->video_image;
            //     }
            //     else
            //     {
            //         $v->video_image =  Helper::default_video_image();
            //     }

            // }
            
                       //  dd($videos);
        return view('store.elements.videos.list',compact('pageTitle','videos'));

            
            
         }catch (\Exception $e) {
             return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
         }catch (\Throwable $e) {
          return redirect()->back()->withErrors(['Something went wrong!'])->withInput();
         } 
    }
    
    
    
    



}



