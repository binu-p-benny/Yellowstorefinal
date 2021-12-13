<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

use App\Models\admin\Mst_store;
use App\Models\admin\Mst_Tax;

use App\Models\admin\Mst_store_product;
use App\Models\admin\Mst_business_types;

use App\Models\admin\Mst_attribute_group;
use App\Models\admin\Mst_attribute_value;

use App\Models\admin\Mst_categories;
use App\Models\admin\Mst_store_agencies;
use App\Models\admin\Mst_SubCategory;
use App\Models\admin\Mst_GlobalProducts;
use App\Models\admin\Mst_store_product_varient;
use App\Models\admin\Trn_ProductVariantAttribute;

use App\Models\admin\Mst_product_image;
use App\Models\admin\Trn_GlobalProductImage;
use App\Models\admin\Trn_RecentlyVisitedProducts;
use App\Models\admin\Trn_RecentlyVisitedStore;
use App\Models\admin\Trn_Cart;
use App\Models\admin\Trn_store_order;
use App\Models\admin\Trn_GlobalProductVideo;
use App\Models\admin\Trn_ProductVideo;

class ProductController extends Controller
{
    
    
    public function setDefaultImage(Request $request)
    {
        //dd($request->all());
       
        
         $data = array(); 
        try {
                if(isset($request->product_image_id) && Mst_product_image::find($request->product_image_id))
                { 
                     if(($request->product_varient_id == 0) || Mst_store_product_varient::find($request->product_varient_id))
                    { 
                    
                         $imageData = Mst_product_image::where('product_image_id',$request->product_image_id)->where('product_varient_id',$request->product_varient_id)->first();
    
                        Mst_product_image::where('product_id',$imageData->product_id)->where('product_varient_id',$request->product_varient_id)->update(['image_flag'=>0]);
                
                        if($request->product_varient_id == 0) 
                        { 
                           
                            Mst_product_image::where('product_image_id',$request->product_image_id)->where('product_varient_id',$request->product_varient_id)->update(['image_flag'=>1]);
                            Mst_store_product::where('product_id',$imageData->product_id)->update(['product_base_image' => $imageData->product_image]);
                        }
                        else
                        {
                            Mst_product_image::where('product_image_id',$request->product_image_id)->where('product_varient_id',$request->product_varient_id)->update(['image_flag'=>1]);
                            Mst_store_product_varient::where('product_varient_id',$imageData->product_varient_id)->update(['product_varient_base_image' => $imageData->product_image]);
                        }
                  
                        $data['status'] = 1;
                        $data['message'] = "Base image updated"; 
                        return response($data);
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "variant not found ";
                        return response($data);
                    }
                    
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Image not found ";
                    return response($data);
                }

        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
        
        

    }
    
    
    
    // list products
    public function list(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                { 
                    $store_id = $request->store_id;
                    if($data['productDetails']  = Mst_store_product::join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')->where('mst_store_products.store_id',$store_id)->orderBy('mst_store_products.product_id', 'DESC')->select('mst_store_products.product_id','mst_store_products.product_name','mst_store_products.product_code','mst_store_products.product_price','mst_store_products.product_price_offer','mst_store_products.product_base_image','mst_store_categories.category_name','mst_store_categories.category_id')->get())
                    {
                        foreach($data['productDetails'] as $product){
                            $product->product_base_image = '/assets/uploads/products/base_product/base_image/'.$product->product_base_image;
                        }
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }

        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listByCategory(Request $request)
    {
        $data = array(); 
        
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                {  
                    $validator = Validator::make($request->all(),
                    [
                        'category_id'          => 'required',
                    ],
                    [
                        'category_id.required'        => 'Category required',
                    ]);
                     
                        if(!$validator->fails() )
                        {
                            if($request->category_id == 0  ||   Mst_categories::find($request->category_id))
                            {
                                $category_id = $request->category_id;
                                $store_id = $request->store_id;
                                if($request->category_id == 0)
                                {
                                    if(1)
                                    {
$productDetails  = Mst_store_product::join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')
                                    ->where('mst_store_products.product_name', 'LIKE', "%{$request->product_name}%")->where('mst_store_products.store_id',$store_id)
                                    ->orderBy('mst_store_products.product_id', 'DESC')
                                        ->select('mst_store_products.product_id','mst_store_products.product_cat_id','mst_store_products.product_name','mst_store_products.product_code','mst_store_products.product_price','mst_store_products.product_price_offer','mst_store_products.product_base_image','mst_store_categories.category_name','mst_store_categories.category_id','mst_store_products.product_status');
                                        if(isset($request->page))
                                            {
                                                $productDetails = $productDetails->paginate(10, ['data'], 'page', $request->page);
                                            }
                                            else
                                            {
                                                $productDetails = $productDetails->paginate(10);
                                            }
                    
                                        foreach($productDetails as $product){
                                            $product->product_base_image = '/assets/uploads/products/base_product/base_image/'.$product->product_base_image;
                                        }
                                        
                                        $data['productDetails'] = $productDetails;
                                        $data['status'] = 1;
                                        $data['message'] = "success";
                                        return response($data);
                                    }
                                    else{
                                        $data['status'] = 0;
                                        $data['message'] = "failed";
                                        return response($data);
                                    }
                                }
                                else
                                {
                                    if(1)
                                    {
                                        
                                      $productDetails =    Mst_store_product::join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')
                                    ->where('mst_store_products.product_name', 'LIKE', "%{$request->product_name}%")
                                    ->where('mst_store_products.product_cat_id',$category_id)->where('mst_store_products.store_id',$store_id)->orderBy('mst_store_products.product_id', 'DESC')
                                    ->select('mst_store_products.product_id','mst_store_products.product_cat_id','mst_store_products.product_name','mst_store_products.product_code','mst_store_products.product_price','mst_store_products.product_price_offer','mst_store_products.product_base_image','mst_store_categories.category_name','mst_store_categories.category_id','mst_store_products.product_status');
                                    
                                        
                                          if(isset($request->page))
                                            {
                                                $productDetails = $productDetails->paginate(10, ['data'], 'page', $request->page);
                                            }
                                            else
                                            {
                                                $productDetails = $productDetails->paginate(10);
                                            }
                                            
                                        foreach($data['productDetails'] as $product){
                                            $product->product_base_image = '/assets/uploads/products/base_product/base_image/'.$product->product_base_image;
                                        }
                                        
                                        $data['productDetails'] = $productDetails;

                                        $data['status'] = 1;
                                        $data['message'] = "success";
                                        return response($data);
                                    }
                                    else
                                    {
                                        $data['status'] = 0;
                                        $data['message'] = "failed";
                                        return response($data);
                                    }
                                }
                                
                            }
                            else
                            {
                                $data['status'] = 0;
                                $data['message'] = "failed";
                                $data['message'] = "Category not found ";
                                return response($data);
                            }
                        }
                        else
                        {
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            $data['message'] = "Category not found ";
                            return response($data);
                        }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }
                
            }catch (\Exception $e) {
            $response = ['status' => '0', 'message' => $e->getMessage()];
            return response($response);
            }catch (\Throwable $e) {
                $response = ['status' => '0','message' => $e->getMessage()];
                return response($response);
            }
    }

    public function storeData(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                { 
                    $store_id = $request->store_id;
                    
                    if($data['storeDetails']  = Mst_store::select('store_name')->find($request->store_id))
                    {
                        $data['storeDetails']->appBanners = Mst_CustomerAppBanner::get();

                            foreach($data['storeDetails']->appBanners as $appBanner){
                                $appBanner->image = '/assets/uploads/customer_banner/'.@$appBanner->image;
                            }
                            $data['status'] = 1;
                            $data['message'] = "success";
                            return response($data);
                    }
                    else{
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }

        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function listProductType(Request $request)
    {
        $data = array(); 
        
        try { 
                
                
            $data['productTypeDetails'] = [
                [
                    'product_type_id' => 1,
                    'product_type_name'=> "Product"
                ],
                [
                    'product_type_id' => 2,
                    'product_type_name'=> "Service"
                ]
            ];

                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
             
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listServiceType(Request $request)
    {
        $data = array(); 
        
        try { 
            $data['serviceTypeDetails'] = [
                [
                    'service_type_id' => 1,
                    'service_type_name'=> "Booking Only"
                ],
                [
                    'service_type_id' => 2,
                    'service_type_name'=> "Purchase"
                ]
            ];

                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
             
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }
    
    
 

    public function listColor(Request $request)
    {
        $data = array(); 
        try {
                if($data['colorDetails']  = Mst_attribute_value::join('mst_attribute_groups','mst_attribute_groups.attr_group_id','=','mst_attribute_values.attribute_group_id')
                    ->where('mst_attribute_groups.group_name','LIKE','%color%')->select('mst_attribute_values.group_value','mst_attribute_values.attr_value_id')->get())
                {
                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
                }
                else{
                    $data['status'] = 0;
                    $data['message'] = "failed";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listAttributeGroup(Request $request)
    {
        $data = array(); 
        try {
                if($data['attributeGroupDetails']  = Mst_attribute_group::select('attr_group_id','group_name')->orderBy('attr_group_id', 'DESC')->get())
                {
                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
                }
                else{
                    $data['status'] = 0;
                    $data['message'] = "failed";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listAttributeValue(Request $request)
    {
        $data = array(); 
        try {
                if($data['attributeValueDetails']  = Mst_attribute_value::select('attr_value_id','group_value')
                ->where('attribute_group_id',$request->attribute_group_id)
                ->orderBy('attr_value_id', 'DESC')->get())
                {
                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
                }
                else{
                    $data['status'] = 0;
                    $data['message'] = "failed";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listProductCategory(Request $request)
    {
        $data = array(); 
        try {
                if($data['productCategoryDetails']  = Mst_categories::
                select('category_id','business_type_id','category_description','category_icon','category_name')
                ->where('category_status', 1)
                ->orderBy('category_id', 'DESC')->get())
                {
                    foreach($data['productCategoryDetails'] as $productCategory){
                        $productCategory->category_icon = '/assets/uploads/category/icons/'.$productCategory->category_icon;
                    }
                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
                }
                else{
                    $data['status'] = 0;
                    $data['message'] = "failed";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listProductSubCategory(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->category_id) && Mst_categories::find($request->category_id))
                {
                    if($data['productSubCategoryDetails']  = Mst_SubCategory::
                    select('sub_category_id','category_id','sub_category_name','sub_category_icon',
                    'sub_category_description')
                    ->where('sub_category_status', 1)
                    ->where('category_id', $request->category_id)
                    ->orderBy('sub_category_id', 'DESC')->get())
                    {
                        foreach($data['productSubCategoryDetails'] as $productCategory){
                            $productCategory->sub_category_icon = '/assets/uploads/category/icons/'.$productCategory->sub_category_icon;
                        }
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Category not found ";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listVendor(Request $request)
    {
        $data = array(); 
        try {
                if($data['agencyDetails']  = Mst_store_agencies::
                select('agency_id','agency_name','agency_contact_person_name','agency_logo')
                ->where('agency_account_status', 1)
                ->orderBy('agency_id', 'DESC')->get())
                {
                    foreach($data['agencyDetails'] as $agency){
                        $agency->agency_logo = '/assets/uploads/agency/logos/'.$agency->agency_logo;
                    }
                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
                }
                else{
                    $data['status'] = 0;
                    $data['message'] = "failed";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listTax(Request $request)
     {
         $data = array(); 
         
         try {
                 if($data['taxDetails']  = Mst_Tax::select('tax_id','tax_name','tax_value')->orderBy('tax_id','DESC')->get())
                 {
                     $data['status'] = 1;
                     $data['message'] = "success";
                     return response($data);
                 }
                 else{
                     $data['status'] = 0;
                     $data['message'] = "failed";
                     return response($data);
                 }
 
         }catch (\Exception $e) {
            $response = ['status' => '0', 'message' => $e->getMessage()];
            return response($response);
         }catch (\Throwable $e) {
             $response = ['status' => '0','message' => $e->getMessage()];
             return response($response);
         }
     }

    public function addProduct(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                {   
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
                        'product_type'   => 'required',
                        'product_cat_id'   => 'required',
                        'vendor_id'   => 'required',
                    // 'product_image.*' => 'required|dimensions:min_width=1000,min_height=800',
                        'product_image.*' => 'required',
                        'product_status'   => 'required',
                       // 'product_brand'   => 'required',
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
                        'product_type.required'        => 'Product type required',
                       // 'attr_group_id.required'        => 'Attribute group required',
                       // 'attr_value_id.required'        => 'Attribute value required',
                        'product_cat_id.required'        => 'Product category required',
                        'sub_category_id.required'        => 'Product sub category required',
                        'vendor_id.required'        => 'Vendor required',
                        'product_brand.required'        => 'Brand required',
                        //'color_id.required'        => 'Color required',
                        'product_status.required'        => 'Staus required',
                        'product_image.required'        => 'Product image required',
                        'product_image.dimensions'        => 'Product image dimensions invalid',
                        ]);

                        if(!$validator->fails())
                        {
                            $product = new Mst_store_product;

                            $product->product_name           = $request->product_name;
                            $product->product_description    = $request->product_description;
                            $product->product_price          = $request->regular_price;
                            $product->product_price_offer    = $request->sale_price;
                            $product->tax_id                 = $request->tax_id; // new
                            
                            $product->stock_count                 = $request->min_stock; // stock count
                            $product->min_stock                 = $request->min_stock; // stock count
                            $product->product_code           = $request->product_code;
                            $product->product_type       = $request->product_type; // product type
                            $product->service_type       = $request->service_type; // new type
                         
                          $product->color_id               = 0; // removed
                          $product->attr_group_id          = 0; // removed
                          $product->attr_value_id          = 0; // removed
                            
                            $product->product_cat_id         = $request->product_cat_id;
                            $product->sub_category_id         = $request->sub_category_id;
                            $product->vendor_id              = $request->vendor_id; // new
                            $product->product_brand              = $request->product_brand; // new
                            
                            $product->product_name_slug      = Str::of($request->product_name)->slug('-');

                            $product->store_id               = $request->store_id;
                            $product->global_product_id      =  @$request->global_product_id; // new
                            
                            if($request->min_stock == 0)
                            {
                                $product->stock_status = 0;
                            }else
                            {
                                $product->stock_status = 1;
                            }
                            $product->business_type_id = 0;
                            $product->product_status         = $request->product_status;
                            
                            if($product->save())
                            {
                                $id = DB::getPdo()->lastInsertId();
                            
                                $c = 1;
                            
                                foreach ($request->product_images as $product_image) {
                                    
                                    $imageData= [
                                        'product_image'      => $product_image,
                                        'product_id' => $id,
                                        'product_varient_id' => 0,
                                        'image_flag'         => 1,
                                        'created_at'         => Carbon::now(),
                                        'updated_at'         => Carbon::now(),
                                        ];
            
                                    Mst_product_image::insert($imageData);
                                        if($c == 1)
                                        {
                                            DB::table('mst_store_products')->where('product_id', $id)
                                            ->update(['product_base_image' => $product_image]);
                                            $c++;
            
                                        }
                                }

                                $vc = 0;
                                $data3 = array();
                                $data4 = array();
                                $date = Carbon::now();
                            
                                foreach ($request->product_variants as $product_variant) {
                                    $sCount = 0;
                                    if($request->product_type == 2)
                                    {
                                        $sCount = 1;
                                    }
                                    $data3 = [
                                        'product_id' => $id,
                                        'store_id' => $request->store_id,
                                        'variant_name' => $product_variant['variant_name'],
                                        'product_varient_price' => $product_variant['var_regular_price'],
                                        'product_varient_offer_price' => $product_variant['var_sale_price'],
                                        'product_varient_base_image' => $product_variant['product_varient_base_image'],
                                        'stock_count' => $sCount,
                                        'color_id' =>  0,
                                        'created_at' => $date,
                                        'updated_at' => $date,
                                      ];
              
                                      Mst_store_product_varient::create($data3);
                                      $vari_id = DB::getPdo()->lastInsertId();

                                    $vac = 0;
                                    foreach($product_variant['variant_attributes'] as $attrGrp)
                                    {
                                        if(($attrGrp['attr_group_id'] != 0) && ($attrGrp['attr_value_id'] != 0))
                                        {
                                            $data4 = [
                                            'product_varient_id' => $vari_id,
                                            'attr_group_id' => $attrGrp['attr_group_id'],
                                            'attr_value_id' => $attrGrp['attr_value_id']
                                            ];
                                            Trn_ProductVariantAttribute::create($data4);
                                        }
                                        $vac++;
                                    }

                                    foreach($product_variant['variant_images'] as $varImg)
                                    {
                                        $imageData= [
                                            'product_image'      => $varImg,
                                            'product_id'         => $id,
                                            'product_varient_id' => $vari_id,
                                            'image_flag'         => 1,
                                            'created_at'         => Carbon::now(),
                                            'updated_at'         => Carbon::now(),
                                            ];
                
                                        Mst_product_image::insert($imageData);

                                    }

                                    
                                    
                                      
                                }


                                $data['status'] = 1;
                                $data['message'] = "Product added successfully.";
                                return response($data);
                            }
                            else
                            {
                                $data['status'] = 0;
                                $data['message'] = "Product insertion failed.";
                                return response($data);
                            }
                        }
                        else
                        {
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            $data['errors'] = $validator->errors();
                            return response($data);
                        }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
        }
    }

    public function listGlobalProductsByVendor(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                if(isset($request->vendor_id) && Mst_store_agencies::find($request->vendor_id))
                {
                    
                    $products_global_products_id = Mst_store_product::where('store_id',$request->store_id)
                    ->where('global_product_id','!=', null)
                    ->orderBy('product_id', 'DESC')
                    ->pluck('global_product_id')
                    ->toArray();
                
                    
                    if($data['globalProductDetails']  = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id)
                    ->select('global_product_id','product_name')
                    ->where('vendor_id',$request->vendor_id)
                    ->orderBy('global_product_id', 'DESC')->get())
                    {
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Vendor not found ";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function listProductVariants(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                if(isset($request->product_id) && Mst_store_product::find($request->product_id))
                {
                    
                    if($data['productVariantsDetails']  = Mst_store_product_varient::where('product_id','=',$request->product_id)
                    ->select('product_varient_id','product_id','store_id','variant_name'
                    ,'product_varient_price','product_varient_offer_price','product_varient_base_image',
                    'stock_count','created_at')
                    ->get())
                    {
                        foreach($data['productVariantsDetails'] as $var)
                        {
                            $var->product_varient_base_image = '/assets/uploads/products/base_product/base_image/'.$var->product_varient_base_image;
                            $var->variantImages = Mst_product_image::where('product_varient_id',$var->product_varient_id)
                            ->select('product_image_id','product_image')
                            ->get();
                            foreach($var->variantImages as $varImg){
                                $varImg->product_image = '/assets/uploads/products/base_product/base_image/'.$varImg->product_image;
                            }
                            $var->varianAttributes = Trn_ProductVariantAttribute::where('product_varient_id',$var->product_varient_id)->get();
                        }
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product not found ";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function editProduct(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                if(isset($request->product_id) && Mst_store_product::find($request->product_id))
                {
                    
                    if($data['prouctDetails'] = Mst_store_product::
                        where('product_id',$request->product_id)
                        ->select(
                            'product_id',
                            'product_name',
                            'product_code',
                            'product_cat_id',
                            'sub_category_id',
                            'product_price',
                            'product_price_offer',
                            'product_description',
                            'product_base_image',
                            'store_id',
                            'product_status',
                            'tax_id',
                            'vendor_id',
                            'show_in_home_screen',
                            'global_product_id',
                            'draft',
                            'product_brand',
                            'product_type',
                            'service_type'
                        )
                        ->first())
                    {
                            @$data['prouctDetails']->product_base_image = '/assets/uploads/products/base_product/base_image/'.@$data['prouctDetails']->product_base_image;

                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product not found ";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function updateProduct(Request $request)
    {
       // dd($request->all());
       
                $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                if(isset($request->product_id) && Mst_store_product::find($request->product_id))
                {
                    $product_id = $request->product_id;
                    $store_id = $request->store_id;
                    $validator = Validator::make($request->all(),
                    [
                        'product_name'          => 'required',
                        'product_description'   => 'required',
                        'regular_price'   => 'required',
                        'sale_price'   => 'required',
                        'tax_id'   => 'required',
                        'min_stock'   => 'required',
                        'product_code'   => 'required',
                        'product_type'   => 'required',
                        'product_cat_id'   => 'required',
                        'vendor_id'   => 'required',
                        'product_status'   => 'required',
                        'product_brand'   => 'required',
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
                        'product_type.required'        => 'Product type required',
                        'product_cat_id.required'        => 'Product category required',
                        'sub_category_id.required'        => 'Product sub category required',
                        'vendor_id.required'        => 'Vendor required',
                        'product_brand.required'        => 'Brand required',
                        'product_status.required'        => 'Staus required',
                        'product_image.required'        => 'Product image required',
                        'product_image.dimensions'        => 'Product image dimensions invalid',
                        ]);

                        if(!$validator->fails())
                        {

                            $product['product_name']           = $request->product_name;
                            $product['product_description']    = $request->product_description;
                            $product['product_price']          = $request->regular_price;
                            $product['product_price_offer']    = $request->sale_price;
                            $product['tax_id']                 = $request->tax_id; // new
                            
                            $product['stock_count']         = $request->min_stock; // stock count
                            $product['product_code']        = $request->product_code;
                            $product['product_type']       = $request->product_type; // product type
                            $product['service_type']       = $request->service_type; // new type
                         
                      
                            
                            $product['product_cat_id']         = $request->product_cat_id;
                            $product['sub_category_id']         = $request->sub_category_id;
                            $product['vendor_id']              = $request->vendor_id; // new
                            $product['product_brand']              = $request->product_brand; // new
                            
                            $product['product_name_slug']      = Str::of($request->product_name)->slug('-');

                            $product['store_id']               = $request->store_id;
                            $product['global_product_id']      =  @$request->global_product_id; // new
                            
                            if($request->min_stock == 0)
                            {
                                $product['stock_status'] = 0;
                            }else
                            {
                                $product['stock_status'] = 1;
                            }
                            $product['business_type_id'] = 0;
                            $product['product_status']         = $request->product_status;
                            
                            if(Mst_store_product::where('product_id',$product_id)->update($product))
                            {
                                $c = 1;//
                                // Mst_product_image::where('product_id',$product_id)
                                // ->where('product_varient_id',0)
                                // ->delete();
                                $filename ="";
                                if ($files = $request->file('product_image')) {
                                    foreach ($files as $file) {
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                    $file->move('assets/uploads/products/base_product/base_image', $filename);

                                        $imageData= [
                                            'product_image'      => $filename,
                                            'product_id' => $product_id,
                                            'product_varient_id' => 0,
                                            'image_flag'         => 1,
                                            'created_at'         => Carbon::now(),
                                            'updated_at'         => Carbon::now(),
                                            ];
                
                                        Mst_product_image::insert($imageData);
                                            if($c == 1)
                                            {
                                                DB::table('mst_store_products')->where('product_id', $product_id)
                                                ->update(['product_base_image' => $filename]);
                                                $c++;
                                            }
                                    }
                                }

                                $vc = 0;
                                $data3 = array();
                                $data4 = array();
                                $date = Carbon::now();
                            
                                foreach ($request->product_variants as $product_variant) {

                                    if($product_variant['product_varient_id'] == null)
                                    {
                                        
                                        $sCount = 0;
                                        if($request->product_type == 2)
                                        {
                                            $sCount = 1;
                                        }
                                    
                                        $data3 = [
                                            'product_id' => $product_id,
                                            'store_id' => $request->store_id,
                                            'variant_name' => $product_variant['variant_name'],
                                            'product_varient_price' => $product_variant['var_regular_price'],
                                            'product_varient_offer_price' => $product_variant['var_sale_price'],
                                            'product_varient_base_image' => $product_variant['product_varient_base_image'],
                                            'stock_count' => $sCount,
                                            'color_id' =>  0,
                                            'created_at' => $date,
                                            'updated_at' => $date,
                                          ];
                  
                                          Mst_store_product_varient::create($data3);
                                          @$vari_id = DB::getPdo()->lastInsertId();
    
                                        $vac = 0;
                                        foreach($product_variant['variant_attributes'] as $attrGrp)
                                        {
                                            if(($attrGrp['attr_group_id'] != 0) && ($attrGrp['attr_value_id'] != 0))
                                            {
                                                $data4 = [
                                                'product_varient_id' => @$vari_id,
                                                'attr_group_id' => $attrGrp['attr_group_id'],
                                                'attr_value_id' => $attrGrp['attr_value_id']
                                                ];
                                                Trn_ProductVariantAttribute::create($data4);
                                            }
                                            $vac++;
                                        }
                                        if ($files2 = $request->file('variant_images')) {
                                            $filename = "";
 
                                            foreach($files2 as $file)
                                            {
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                                $file->move('assets/uploads/products/base_product/base_image', $filename);
                                                $imageData1= [
                                                    'product_image'      => $filename,
                                                    'product_id'         => $product_id,
                                                    'product_varient_id' => @$vari_id,
                                                    'image_flag'         => 1,
                                                    'created_at'         => Carbon::now(),
                                                    'updated_at'         => Carbon::now(),
                                                    ];
                                                Mst_product_image::insert($imageData1);
                                            }
                                        }

                                    }
                                    else
                                    {
                                        $data30 = [
                                            'variant_name' => $product_variant['variant_name'],
                                            'product_varient_price' => $product_variant['var_regular_price'],
                                            'product_varient_offer_price' => $product_variant['var_sale_price'],
                                            'product_varient_base_image' => $product_variant['product_varient_base_image'],
                                          ];
                                          Mst_store_product_varient::where('product_varient_id',$product_variant['product_varient_id'])->update($data30);
                                          Trn_ProductVariantAttribute::where('product_varient_id',@$product_variant['product_varient_id'])->delete();

                                          $vac = 0;
                                          foreach($product_variant['variant_attributes'] as $attrGrp)
                                          {
                                                if(($attrGrp['attr_group_id'] != 0) && ($attrGrp['attr_value_id'] != 0))
                                                {
                                                  $data4 = [
                                                  'product_varient_id' => @$product_variant['product_varient_id'],
                                                  'attr_group_id' => $attrGrp['attr_group_id'],
                                                  'attr_value_id' => $attrGrp['attr_value_id']
                                                  ];
                                                  Trn_ProductVariantAttribute::create($data4);
                                                }
                                              $vac++;
                                          }
                                        //   Mst_product_image::where('product_id',$product_id)
                                        //   ->where('product_varient_id',@$product_variant['product_varient_id'])
                                        //   ->delete();
                                        
                                        $data['status'] = 1;
                                        $data['message'] = $request->file('variant_images');
                                        return response($data);
                                                
                                          if ($files2 = $request->file('variant_images')) {
                                            $filename = "";
                                            $c = 1;
 
                                            foreach($files2 as $file)
                                            {
                                                
                                
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                                $file->move('assets/uploads/products/base_product/base_image', $filename);
                                                $imageData1= [
                                                    'product_image'      => $filename,
                                                    'product_id'         => $product_id,
                                                    'product_varient_id' => @$product_variant['product_varient_id'],
                                                    'image_flag'         => 1,
                                                    'created_at'         => Carbon::now(),
                                                    'updated_at'         => Carbon::now(),
                                                    ];
                                                Mst_product_image::insert($imageData1); 
                                                
                                                 if($c == 1)
                                                 {
                                                     DB::table('mst_store_product_varients')
                                                            ->where('product_varient_id', $product_variant['product_varient_id'])
                                                            ->update(['product_varient_base_image' => $filename]);
                                                            $c++;
                                                 }
                                            }
                                        }
                                        
                                    }
                                    
                                }
                                $data['status'] = 1;
                                $data['message'] = "Product updated successfully.";
                                return response($data);
                            }
                            else
                            {
                                $data['status'] = 0;
                                $data['message'] = "Product update failed.";
                                return response($data);
                            }
                        }
                        else
                        {
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            $data['errors'] = $validator->errors();
                            return response($data);
                        }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product not found ";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function removeProduct(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                if(isset($request->product_id) && Mst_store_product::find($request->product_id))
                {
                    
                    if(Mst_store_product::where('product_id',$request->product_id)->delete())
                    {
                        Mst_store_product_varient::where('product_id',$request->product_id)->delete();
                        $data['status'] = 1;
                        $data['message'] = "Product deleted ";
                        return response($data);
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product not found ";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }
    
     public function removeProductImage(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->product_image_id) && Mst_product_image::find($request->product_image_id))
            {
               
                    if(1)
                    {
                        $proImageData = Mst_product_image::find($request->product_image_id);
                        Mst_product_image::where('product_image_id',$request->product_image_id)->delete();

                        if($proImageData->product_varient_id == 0)
                        {
                            $secImg = Mst_product_image::where('product_id',$proImageData->product_id)->where('product_varient_id',0)->first();
                            $thirdImg = Mst_product_image::where('product_id',$proImageData->product_id)->first();
                            if($secImg)
                            {
                                Mst_store_product::where('product_id',$proImageData->product_id)->update(['product_base_image' => $secImg->product_image]);
                            }
                            elseif($thirdImg)
                            {
                                Mst_store_product::where('product_id',$proImageData->product_id)->update(['product_base_image' => $thirdImg->product_image]);
                            }else
                            {
                                Mst_store_product::where('product_id',$proImageData->product_id)->update(['product_base_image' => null]);
                            }
                        }
                        else
                        {
                            $secImg = Mst_product_image::where('product_id',$proImageData->product_id)
                            ->where('product_varient_id',$proImageData->product_varient_id)->first();
                            if($secImg)
                            {
                                Mst_store_product_varient::where('product_varient_id',$proImageData->product_varient_id)
                                ->update(['product_varient_base_image' => $secImg->product_image]);
                                
                                Mst_product_image::where('product_image_id',$secImg->product_image_id)
                                ->update(['image_flag' => 1]);

                            }
                            else
                            {
                                Mst_store_product_varient::where('product_varient_id',$proImageData->product_varient_id)
                                ->update(['product_varient_base_image' => null]);
                            }

                        }
                      //  dd($proImageData);
                        
                        $data['status'] = 1;
                        $data['message'] = "Product image deleted ";
                        return response($data);
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
              
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Product image not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function singleVariant(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->product_varient_id) && Mst_store_product_varient::find($request->product_varient_id))
                {
                    if($data['productVariantsDetails']  = Mst_store_product_varient::where('product_varient_id','=',$request->product_varient_id)
                    ->select('product_varient_id','product_id','store_id','variant_name'
                    ,'product_varient_price','product_varient_offer_price','product_varient_base_image',
                    'stock_count','created_at')
                    ->first())
                    {
                        $data['productVariantsDetails']->product_varient_base_image = '/assets/uploads/products/base_product/base_image/'.$data['productVariantsDetails']->product_varient_base_image;
                        $data['productVariantsDetails']->variantImages = Mst_product_image::where('product_varient_id',$data['productVariantsDetails']->product_varient_id)
                            ->select('product_image_id','product_image')
                            ->get();
                            foreach($data['productVariantsDetails']->variantImages as $varImg){
                                $varImg->product_image = '/assets/uploads/products/base_product/base_image/'.$varImg->product_image;
                            }
                            $data['productVariantsDetails']->varianAttributes = Trn_ProductVariantAttribute::where('product_varient_id',$data['productVariantsDetails']->product_varient_id)->get();
                        
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product variant not found ";
                    return response($data);
                }
            
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function removeVariant(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->product_varient_id) && Mst_store_product_varient::find($request->product_varient_id))
                {
                    
                    if($productVar = Mst_store_product_varient::where('product_varient_id',$request->product_varient_id)->first())
                    {
                        Mst_store_product_varient::where('product_varient_id',$request->product_varient_id)->delete();
                        $productVarCount = Mst_store_product_varient::where('product_id',$productVar->product_id)->count();
            
                       if($productVarCount < 1)
                       {
                           Mst_store_product::where('product_id',$productVar->product_id)->update(['product_status' => 0]);
                       }
           
                        $data['status'] = 1;
                        $data['message'] = "Product variant deleted ";
                        return response($data);
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product variant not found ";
                    return response($data);
                }
            
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function listVariantAttr(Request $request)
    {
        $data = array(); 
        try {
           
                if(isset($request->product_varient_id) && Mst_store_product_varient::find($request->product_varient_id))
                {
                    
                    if($data['variantAttributesDetails']  = Trn_ProductVariantAttribute::where('product_varient_id','=',$request->product_varient_id)
                    ->select('product_varient_id','variant_attribute_id','attr_group_id','attr_value_id')
                    ->get())
                    {
                        foreach($data['variantAttributesDetails'] as $var)
                        {
                            $attrValue = Mst_attribute_value::where('attr_value_id',$var->attr_value_id)->first();
                            $attrGroup = Mst_attribute_group::where('attr_group_id',$var->attr_group_id)->first();
                            $var->variant_attribute_group = @$attrGroup->group_name;
                            $var->variant_attribute_value = @$attrValue->group_value;
                        }
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product variant not found ";
                    return response($data);
                }
           
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function addVariantAttr(Request $request)
    {
        $data = array(); 
        try {
           
                if(isset($request->product_varient_id) && Mst_store_product_varient::find($request->product_varient_id))
                {
                    
                        $validator = Validator::make($request->all(),
                        [
                            'attr_group_id'          => 'required',
                            'attr_value_id'          => 'required',
                        ],
                        [
                            'attr_group_id.required'             => 'Attribute group required',
                            'attr_value_id.required'             => 'Attribute value required',
                        ]);

                        if(!$validator->fails())
                        {
                            $attr = new Trn_ProductVariantAttribute;

                            $attr->product_varient_id           = $request->product_varient_id;
                            $attr->attr_group_id           = $request->attr_group_id;
                            $attr->attr_value_id    = $request->attr_value_id;
                            
                            if($attr->save())
                            {
                                $data['status'] = 1;
                                $data['message'] = "Attribute added successfully.";
                                return response($data);
                            }
                            else
                            {
                                $data['status'] = 0;
                                $data['message'] = "failed";
                                return response($data);
                            }
                        }
                        else
                        {
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            $data['errors'] = $validator->errors();
                            return response($data);
                        }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product variant not found ";
                    return response($data);
                }
           
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function removeVariantAttr(Request $request)
    {
        $data = array(); 
        try {
           
                if(isset($request->variant_attribute_id) && Trn_ProductVariantAttribute::find($request->variant_attribute_id))
                {
                    if(Trn_ProductVariantAttribute::where('variant_attribute_id',$request->variant_attribute_id)->delete())
                    {
                        $data['status'] = 1;
                        $data['message'] = "Variant attribute deleted ";
                        return response($data);
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    } 
                      
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Variant attribute not found ";
                    return response($data);
                }
           
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function viewProduct(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                if(isset($request->product_id) && Mst_store_product::find($request->product_id))
                {
                    
                    if($data['prouctDetails'] = Mst_store_product::
                        where('product_id',$request->product_id)
                        ->where('store_id',$request->store_id)
                        ->select(
                            'product_id',
                            'product_name',
                            'product_code',
                            'product_cat_id',
                            'sub_category_id',
                             'stock_count',
                            'product_price',
                            'product_price_offer',
                            'product_description',
                            'product_base_image',
                            'store_id',
                            'product_status',
                            'tax_id',
                            'vendor_id',
                            'show_in_home_screen',
                            'global_product_id',
                            'draft',
                            'product_brand',
                            'product_type',
                            'service_type',
                        )
                        ->first())
                    {
                        @$data['prouctDetails']->product_description =  strip_tags(@$data['prouctDetails']->product_description);

                        $catData =  Mst_categories::find($data['prouctDetails']->product_cat_id);
                        $subCatData =  Mst_SubCategory::find($data['prouctDetails']->sub_category_id);
                        $tax =  Mst_Tax::find($data['prouctDetails']->tax_id);
                        $vendor =  Mst_store_agencies::find($data['prouctDetails']->vendor_id);
                        $glbPro =  Mst_GlobalProducts::find($data['prouctDetails']->global_product_id);

                            @$data['prouctDetails']->category_name = $catData->category_name;
                            @$data['prouctDetails']->sub_category_name = $subCatData->sub_category_name;
                            @$data['prouctDetails']->tax_name = @$tax->tax_name;
                            @$data['prouctDetails']->tax_value = @$tax->tax_value;
                            @$data['prouctDetails']->vendor = @$vendor->agency_name;
                            @$data['prouctDetails']->min_stock =  @$data['prouctDetails']->stock_count;
                            @$data['prouctDetails']->global_product = @$glbPro->product_name;

                            if(@$data['prouctDetails']->product_type == 1)
                            {
                                @$data['prouctDetails']->product_type_name = "Product";
                            }elseif(@$data['prouctDetails']->product_type == 2)
                            {
                                @$data['prouctDetails']->product_type_name = "Service";
                            }
                            else
                            {
                                @$data['prouctDetails']->product_type_name = null;

                            }
 
                            if(@$data['prouctDetails']->service_type == 1)
                            {
                                @$data['prouctDetails']->service_type_name = "Booking Only";
                            }elseif(@$data['prouctDetails']->service_type == 2)
                            {
                                @$data['prouctDetails']->service_type_name = "Purchase";
                            }
                            else
                            {
                                @$data['prouctDetails']->service_type_name = null;
                            }

                            @$data['prouctDetails']->product_base_image = '/assets/uploads/products/base_product/base_image/'.@$data['prouctDetails']->product_base_image;
                            $data['prouctDetails']->prouctVariantDetails = Mst_store_product_varient::where('product_id',$request->product_id)->get();
                            $data['prouctDetails']->productImages = Mst_product_image::where('product_id',$request->product_id)->where('product_varient_id',0)->get();
                            foreach($data['prouctDetails']->productImages as $val)
                            {
                                @$val->product_image = '/assets/uploads/products/base_product/base_image/'.@$val->product_image;
                            }
                            foreach($data['prouctDetails']->prouctVariantDetails as $key)
                            {
                                @$key->product_varient_base_image = '/assets/uploads/products/base_product/base_image/'.@$key->product_varient_base_image;
                                @$key->variantAttributes = Trn_ProductVariantAttribute::where('product_varient_id',$key->product_varient_id)->get();
                                foreach(@$key->variantAttributes as $v)
                                {
                                    $aG  = Mst_attribute_group::find(@$v->attr_group_id);
                                    $aV = Mst_attribute_value::find(@$v->attr_value_id);
                                    $v->att_group = @$aG->group_name;
                                    $v->att_val = @$aV->group_value;
                                   

                                }
                                 $key->variantImages = Mst_product_image::where('product_id',$request->product_id)
                                    ->where('product_varient_id',$key->product_varient_id)
                                    ->get();
                                    foreach(@$key->variantImages as $img)
                                    {
                                        @$img->product_image = '/assets/uploads/products/base_product/base_image/'.@$img->product_image;
                                    }
                            }
                        $data['status'] = 1;
                        $data['message'] = "success";
                        return response($data);
                    }
                    else{
                        $data['status'] = 0;
                        $data['message'] = "failed";
                        return response($data);
                    }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product not found ";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found ";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }






    //// NEW PART

    public function addProductn(Request $request)
    {  // echo "here";die;
       // dd($request->all());
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                {   
                    $validator = Validator::make($request->all(),
                    [
                    // 'product_name'          => 'required|unique:mst_store_products,product_name,'.$store_id.',store_id',
                        'product_name'          => 'required',
                        'product_description'   => 'required',
                        'regular_price'   => 'required',
                        'sale_price'   => 'required',
                        'tax_id'   => 'required',
                        'product_code'   => 'required',
                        'product_type'   => 'required',
                        'product_cat_id'   => 'required',
                        'vendor_id'   => 'required',
                    // 'product_image.*' => 'required|dimensions:min_width=1000,min_height=800',
                        'product_image.*' => 'required',
                        'product_status'   => 'required',
                       // 'product_brand'   => 'required',
                    ],
                    [
                        'product_name.required'             => 'Product name required',
                        'product_name.unique'             => 'Product name already exist',
                        'product_description.required'      => 'Product description required',
                        'regular_price.required'      => 'Regular price required',
                        'sale_price.required'      => 'Sale price required',
                        'tax_id.required'      => 'Tax required',
                        'product_code.required'      => 'Product code required',
                        'product_type.required'        => 'Product type required',
                       // 'attr_group_id.required'        => 'Attribute group required',
                       // 'attr_value_id.required'        => 'Attribute value required',
                        'product_cat_id.required'        => 'Product category required',
                        'sub_category_id.required'        => 'Product sub category required',
                        'vendor_id.required'        => 'Vendor required',
                        'product_brand.required'        => 'Brand required',
                        //'color_id.required'        => 'Color required',
                        'product_status.required'        => 'Staus required',
                        'product_image.required'        => 'Product image required',
                        'product_image.dimensions'        => 'Product image dimensions invalid',
                        ]);

                        if(!$validator->fails())
                        {
                            if($request->product_id == 0)
                            {
                                    $product = new Mst_store_product;

                                    $product->product_name           = $request->product_name;
                                    $product->product_description    = $request->product_description;
                                    $product->product_price          = $request->regular_price;
                                    $product->product_price_offer    = $request->sale_price;
                                    $product->tax_id                 = $request->tax_id; // new
                                    
                                    $product->stock_count                 = $request->min_stock; // stock count
                                    $product->product_code           = $request->product_code;
                                    $product->product_type       = $request->product_type; // product type
                                    $product->service_type       = $request->service_type; // new type
                                
                                $product->color_id               = 0; // removed
                                $product->attr_group_id          = 0; // removed
                                $product->attr_value_id          = 0; // removed
                                $product->stock_status          = 0; // removed
                                $product->business_type_id = 0; // removed
                                    
                                    $product->product_cat_id         = $request->product_cat_id;
                                    $product->sub_category_id         = $request->sub_category_id;
                                    $product->vendor_id              = $request->vendor_id; // new
                                    $product->product_brand              = $request->product_brand; // new
                                    
                                    $product->product_name_slug      = Str::of($request->product_name)->slug('-');
                                    $product->store_id               = $request->store_id;
                                    $product->global_product_id      =  @$request->global_product_id; // new
                                    $product->product_status         = $request->product_status;
                                    
                                    if($product->save())
                                    {
                                        $id = DB::getPdo()->lastInsertId();
                                        $c = 1;
                                        $filename ="";
                                        if ($files = $request->file('product_images')) {
                                        // dd($files);
                                            foreach($files as $file) {
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                        $file->move('assets/uploads/products/base_product/base_image', $filename);
                                                $imageData= [
                                                    'product_image'      => $filename,
                                                    'product_id' => $id,
                                                    'product_varient_id' => 0,
                                                    'image_flag'         => 0,
                                                    'created_at'         => Carbon::now(),
                                                    'updated_at'         => Carbon::now(),
                                                    ];
                        
                                                Mst_product_image::insert($imageData);
                                         $proImg_Id = DB::getPdo()->lastInsertId();

                                                    if($c == 1)
                                                    {
                                                        DB::table('mst_store_products')->where('product_id', $id)
                                                        ->update(['product_base_image' => $filename]);
                                                        $c++;
                        DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);

                                                    }
                                            }
                                        }

                                        if($request->c == 'zero')
                                        {
                                          
                                              $productVar = new Mst_store_product_varient;

                                                $productData = Mst_store_product::find($id);
                
                                                $productVar->product_id           = $id;
                                                $productVar->store_id    = @$productData->store_id;
                                                $productVar->variant_name          = $request->product_name;
                                                $productVar->product_varient_price    = $request->regular_price;
                                                $productVar->product_varient_offer_price    = $request->sale_price;
                                                $productVar->product_varient_base_image = null;
                                                
                                    if($request->product_type == 2)
                                    {
                                                $productVar->stock_count                 = 1; 
                                    }
                                    else
                                    {
                                                $productVar->stock_count                 = 0; 

                                    }
                                    
                
                                                if($productVar->save())
                                                {
                                                    $Varid = DB::getPdo()->lastInsertId();
                                                    
                                                    $data['product_variant_id'] = $Varid;
                                                    
                                                     $c = 1;

                                                      $product_images = Mst_product_image::where('product_id',$id)->get();

                                                        foreach ($product_images as $file) 
                                                        {
                                
                                                            $date = Carbon::now();
                                                            $data1= [[
                                                                    'product_image'      => $file->product_image,
                                                                    'product_id' => $id,
                                                                    'product_varient_id' => $Varid,
                                                                    'image_flag'         => 0,
                                                                    'created_at'         => Carbon::now(),
                                                                    'updated_at'         => Carbon::now(),
                                                                        ],
                                                                        ];
                                                                Mst_product_image::insert($data1);
                                                                $proImg_Id = DB::getPdo()->lastInsertId();

                                                                if($c == 1)
                                                                {
                                                                        DB::table('mst_store_product_varients')
                                                                        ->where('product_varient_id', $Varid)
                                                                        ->update(['product_varient_base_image' => $file->product_image]);
                                                                        $c++;
                        DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);

                                                                }
                                                        }
                                                }
                                        }

                                        $data['status'] = 1;
                                        $data['product_id'] = $id;
                                        $data['message'] = "Success.";
                                        return response($data);
                                    }
                                    else
                                    {
                                        $data['status'] = 0;
                                        $data['message'] = "Product insertion failed.";
                                        return response($data);
                                    }
                                }
                                else
                                {
                                    if(Mst_store_product::find($request->product_id))
                                    {    
                                        $productData['product_name'] = $request->product_name;
                                        $productData['product_description'] = $request->product_description;
                                        $productData['product_price'] = $request->regular_price;
                                        $productData['product_price_offer'] = $request->sale_price;
                                        $productData['tax_id'] = $request->tax_id;
                                        
                                        $productData['stock_count'] = $request->min_stock;
                                        $productData['product_code'] = $request->product_code;
                                        $productData['product_type'] = $request->product_type;
                                        $productData['service_type'] = $request->service_type;

                                        $productData['product_cat_id'] = $request->product_cat_id;
                                        $productData['sub_category_id'] = $request->sub_category_id;
                                        $productData['vendor_id'] = $request->vendor_id;
                                        $productData['product_brand'] = $request->product_brand;
                                        $productData['product_status'] = $request->product_status;
                                        
                                        $varCount = Mst_store_product_varient::where('product_id',$request->product_id)->count();
                                        
                                        if($request->c != 'other')
                                        {
                                            if(($varCount < 1) && ($request->product_status == 1))
                                            {
                                                $data['status'] = 2;
                                                $data['message'] = "No variant exists.";
                                                return response($data); 
                                            }
                                        }

                                        if(Mst_store_product::where('product_id',$request->product_id)->update($productData))
                                        {
                                            $c = 1;
                                            $filename ="";
                                            if ($files = $request->file('product_images')) {
                                            // dd($files);
                                        //    Mst_product_image::where('product_id',$request->product_id)->where('product_varient_id',0)->delete();

                                                foreach($files as $file) {
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                                $file->move('assets/uploads/products/base_product/base_image', $filename);
                                                    $imageData= [
                                                        'product_image'      => $filename,
                                                        'product_id' => $request->product_id,
                                                        'product_varient_id' => 0,
                                                        'image_flag'         => 0,
                                                        'created_at'         => Carbon::now(),
                                                        'updated_at'         => Carbon::now(),
                                                        ];
                            
                                                    Mst_product_image::insert($imageData);
                                                    $proImg_Id = DB::getPdo()->lastInsertId();
                                                    $proData = Mst_store_product::where('product_id',$request->product_id)->first();
                                                    if(!isset($proData->product_base_image))
                                                    {
                                                        if($c == 1)
                                                        {
                                                            DB::table('mst_store_products')->where('product_id', $request->product_id)
                                                            ->update(['product_base_image' => $filename]);
                                                            $c++;
                                                            DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                                                        }
                                                    }
                                                }
                                            }

                                            $data['status'] = 1;
                                            $data['product_id'] = $request->product_id;
                                            $data['message'] = "Success.";
                                            return response($data);
                                        }
                                    }
                                    else
                                    {
                                        $data['status'] = 0;
                                        $data['message'] = "Product not found ";
                                        return response($data);
                                    }

                                }
                        }
                        else
                        {
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            $data['errors'] = $validator->errors();
                            return response($data);
                        }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }
            
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
        }
    }
    
    
    

    public function addProductVariants(Request $request)
    {
        //dd($request->all());
        $data = array(); 
        try {
                if(isset($request->product_id) && Mst_store_product::find($request->product_id))
                {   
                    $validator = Validator::make($request->all(),
                    [
                    //    'variant_name'          => 'required',
                     //   'var_regular_price'   => 'required',
                     //   'var_sale_price'   => 'required',
                     
                    ],
                    [
                        'variant_name.required'             => 'Variant name required',
                        'var_regular_price.required'             => 'Regular price required',
                        'var_sale_price.required'             => 'Sale price required',
                        ]);

                        if(!$validator->fails())
                        {
                           if($request->product_varient_id == 0 )
                           {
                                $productVar = new Mst_store_product_varient;

                                $productData = Mst_store_product::find($request->product_id);

                                $productVar->product_id           = $request->product_id;
                                $productVar->store_id    = @$productData->store_id;
                                $productVar->variant_name          = $request->variant_name;
                                $productVar->product_varient_price    = $request->var_regular_price;
                                $productVar->product_varient_offer_price    = $request->var_sale_price;
                                $productVar->product_varient_base_image = null;
                                
                                if($productData->product_type == 2)
                                    {
                                                $productVar->stock_count                 = 1; 
                                    }
                                    else
                                    {
                                                $productVar->stock_count                 = 0; 

                                    }
                                    

                                if($productVar->save())
                                {
                                    $Varid = DB::getPdo()->lastInsertId();

                                    $vac = 0; 

                                    $VarArrts = html_entity_decode($request->variant_attributes);
                                    $myArray = json_decode($VarArrts, true);
                                    
                                    foreach($myArray as $varAttr)
                                    {
                                        $data4 = [];
                                        if(($varAttr['attr_group_id'] != 0) && ($varAttr['attr_value_id'] != 0))
                                        {
                                            $data4 = [
                                            'product_varient_id' => $Varid,
                                            'attr_group_id' => $varAttr['attr_group_id'],
                                            'attr_value_id' => $varAttr['attr_value_id']
                                            ];
                                            Trn_ProductVariantAttribute::create($data4);
                                        }
                                        $vac++;
                                    }
                                    

                                    $c = 1;
                                    $filename ="";
                                    if ($files = $request->file('variant_images')) {
                                        foreach($files as $file) {
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                        $file->move('assets/uploads/products/base_product/base_image', $filename);
                                
                                        $imageData= [
                                            'product_image'      => $filename,
                                            'product_id'         => $request->product_id,
                                            'product_varient_id' => $Varid,
                                            'image_flag'         => 0,
                                            'created_at'         => Carbon::now(),
                                            'updated_at'         => Carbon::now(),
                                        ];
                                        
                                        Mst_product_image::insert($imageData);
                                         $proImg_Id = DB::getPdo()->lastInsertId();

                                                if($c == 1)
                                                {
                                                    DB::table('mst_store_product_varients')->where('product_varient_id', $Varid)
                                                    ->update(['product_varient_base_image' => $filename]);
                                                    $c++;
                        DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                                                }
                                        }
                                    }

                                    $data['status'] = 1;
                                    $data['product_id'] = $request->product_id;
                                    $data['product_varient_id'] = $Varid;
                                    $data['message'] = "Success.";
                                    return response($data);
                                }
                                else
                                {
                                    $data['status'] = 0;
                                    $data['message'] = "Product variant insertion failed.";
                                    return response($data);
                                }
                            }
                            else
                            {
                                $product_varient_id = $request->product_varient_id;

                                $productVar['variant_name'] = $request->variant_name;
                                $productVar['product_varient_price'] = $request->var_regular_price;
                                $productVar['product_varient_offer_price'] = $request->var_sale_price;
                                if(Mst_store_product_varient::where('product_varient_id',$product_varient_id)->update($productVar))
                                {
                                    $vac = 0;
                                    Trn_ProductVariantAttribute::where('product_varient_id',@$product_varient_id)->delete();
                                    
                                    $VarArrts = html_entity_decode(@$request->variant_attributes);
                                    $myArray = json_decode(@$VarArrts, true);
                                    
                                    foreach($myArray as $varAttr)
                                    {
                                        $data4 = [];
                                        
                                        if(($varAttr['attr_group_id'] != 0) && ($varAttr['attr_value_id'] != 0))
                                        {

                                            $data4 = [
                                                'product_varient_id' => $product_varient_id,
                                                'attr_group_id' => $varAttr['attr_group_id'],
                                                'attr_value_id' => $varAttr['attr_value_id']
                                            ];
                                            Trn_ProductVariantAttribute::create($data4);
                                        }
                                        $vac++;
                                    }


                                    $c = 1;
                                    $filename ="";
                                    if ($files = $request->file('variant_images')) 
                                    {
                                        foreach($files as $file) 
                                        {
                                                $filename = rand(1,5000).time() . '.' . $file->getClientOriginalExtension();
                                            $file->move('assets/uploads/products/base_product/base_image', $filename);
                                            $imageData= [
                                                'product_image'      => $filename,
                                                'product_id'         => $request->product_id,
                                                'product_varient_id' => $product_varient_id,
                                                'image_flag'         => 0,
                                                'created_at'         => Carbon::now(),
                                                'updated_at'         => Carbon::now(),
                                            ];
                                            Mst_product_image::insert($imageData);
                                         $proImg_Id = DB::getPdo()->lastInsertId();
                                         
                                            if($c == 1)
                                            {
                                                if(DB::table('mst_product_images')->where('product_varient_id', $product_varient_id)->where('image_flag', 1)->first())
                                                {
                                                }
                                                else
                                                {
                                                     DB::table('mst_store_product_varients')->where('product_varient_id', $product_varient_id)->update(['product_varient_base_image' => $filename]);
                                                    
                                                    DB::table('mst_product_images')->where('product_image_id', $proImg_Id)->update(['image_flag' => 1]);
                                                
                                                    $c++;
                                                }
                                                   
    
                                            }

                                        }
                                    }

                                    $data['status'] = 1;
                                    $data['product_id'] = $request->product_id;
                                    $data['product_varient_id'] = $product_varient_id;
                                    $data['message'] = "Success.";
                                    return response($data);
                                }
                            }
                        }
                        else
                        {
                            $data['status'] = 0;
                            $data['message'] = "failed";
                            $data['errors'] = $validator->errors();
                            return response($data);
                        }
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Product not found ";
                    return response($data);
                }
            
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
        }
    }


    public function listGlobalProduct(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                {
                    if(isset($request->category_id) && Mst_categories::find($request->category_id))
                    {
                                
                        $products_global_products_id = Mst_store_product::where('store_id',$request->store_id)->where('global_product_id','!=', null)->orderBy('product_id', 'DESC')->pluck('global_product_id')->toArray();      
                        
                        $query  = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id)->where('product_cat_id',$request->category_id);
                              
                        if(isset($request->product_name))
                        {
                            $query  = $query->where('product_name', 'LIKE', "%{$request->product_name}%");

                        }
                        
                        $data['globalProductDetails'] = $query->orderBy('global_product_id','DESC')->get();
                              
                              
                                            foreach($data['globalProductDetails'] as $product){
                                                $catData =  Mst_categories::find($product->product_cat_id);

                                                $product->product_base_image = '/assets/uploads/products/base_product/base_image/'.$product->product_base_image;
                                                $taxData = Mst_Tax::find(@$product->tax_id);
                                                $product->tax_name = @$taxData->tax_name;
                                                $product->tax_value = @$taxData->tax_value;
                                                @$product->category_name = $catData->category_name;
                                            }
                                            $data['status'] = 1;
                                            $data['message'] = "success";
                                            return response($data);
                              
                                
                    }
                    else
                    {
                       
                                    
                          $products_global_products_id = Mst_store_product::where('store_id',$request->store_id)->where('global_product_id','!=', null)->orderBy('product_id', 'DESC')->pluck('global_product_id')->toArray();        
                        

                            $query  = Mst_GlobalProducts::whereNotIn('global_product_id',$products_global_products_id);
                              
                        if(isset($request->product_name))
                        {
                            $query  = $query->where('product_name', 'LIKE', "%{$request->product_name}%"); 

                        }
                        
                        $data['globalProductDetails'] = $query->orderBy('global_product_id','DESC')->get();
                            
                                    foreach($data['globalProductDetails'] as $product){
                                        $catData =  Mst_categories::find($product->product_cat_id);
                                        $product->product_base_image = '/assets/uploads/products/base_product/base_image/'.$product->product_base_image;
                                        $taxData = Mst_Tax::find(@$product->tax_id);
                                        $product->tax_name = @$taxData->tax_name;
                                        $product->tax_value = @$taxData->tax_value;
                                        @$product->category_name = $catData->category_name;
                                    }
                                    $data['status'] = 1;
                                    $data['message'] = "success";
                                    return response($data);
                       
                       
                    }    
                        
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }

        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }

    public function viewGlobalProduct(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                {
                    if(isset($request->global_product_id) && Mst_GlobalProducts::find($request->global_product_id))
                    {
                                if($data['prouctDetails'] = Mst_GlobalProducts::find($request->global_product_id))
                                {
                                            
                                    
                                                $catData =  Mst_categories::find($data['prouctDetails']->product_cat_id);
                                                $subCatData =  Mst_SubCategory::find($data['prouctDetails']->sub_category_id);
                                                $tax =  Mst_Tax::find($data['prouctDetails']->tax_id);
                                                $vendor =  Mst_store_agencies::find($data['prouctDetails']->vendor_id);
                        
                                                    @$data['prouctDetails']->category_name = $catData->category_name;
                                                    @$data['prouctDetails']->sub_category_name = $subCatData->sub_category_name;
                                                    @$data['prouctDetails']->tax_name = @$tax->tax_name;
                                                    @$data['prouctDetails']->tax_value = @$tax->tax_value;
                                                    @$data['prouctDetails']->vendor = @$vendor->agency_name;
                                                    @$data['prouctDetails']->min_stock = 0;
                        
                                                    if(@$data['prouctDetails']->product_type == 1)
                                                    {
                                                        @$data['prouctDetails']->product_type_name = "Product";
                                                    }elseif(@$data['prouctDetails']->product_type == 2)
                                                    {
                                                        @$data['prouctDetails']->product_type_name = "Service";
                                                    }
                        
                                                    if(@$data['prouctDetails']->service_type == 1)
                                                    {
                                                        @$data['prouctDetails']->service_type_name = "Booking Only";
                                                    }elseif(@$data['prouctDetails']->service_type == 2)
                                                    {
                                                        @$data['prouctDetails']->service_type_name = "Purchase";
                                                    }
                        
                                                    @$data['prouctDetails']->product_base_image = '/assets/uploads/products/base_product/base_image/'.@$data['prouctDetails']->product_base_image;
                                                    $data['prouctDetails']->productImages = Trn_GlobalProductImage::where('global_product_id',$request->global_product_id)->get();
                                                    foreach($data['prouctDetails']->productImages as $val)
                                                    {
                                                        @$val->image_name = '/assets/uploads/products/base_product/base_image/'.@$val->image_name;
                                                    }
                        


                                            $data['status'] = 1;
                                            $data['message'] = "success";
                                            return response($data);
                                }
                                else
                                {
                                            $data['status'] = 0;
                                            $data['message'] = "failed";
                                            return response($data);
                                }
                                
                    }
                    else
                    {
                        $data['status'] = 0;
                        $data['message'] = "Product not found ";
                        return response($data);
                    }
                    
                        
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }

        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }


    public function convertGlobalProduct(Request $request)
    {
        $data = array(); 
        try {
                if(isset($request->store_id) && Mst_store::find($request->store_id))
                {
                   
                    foreach($request->global_product_id as $global_product_id)
                    {
                
                    

                            $global_product = Mst_GlobalProducts::find($global_product_id);
                            $store_id =  $request->store_id;



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

                        $product['product_description'] = $global_product->product_description;
                        $product['product_base_image'] = $global_product->product_base_image;
                        $product['store_id'] = $store_id;
                        $product['stock_count'] = $global_product->min_stock;
                        $product['global_product_id'] = $global_product_id;
                        $product['product_brand'] = $global_product->product_brand;;

                        $product['product_type'] = 1;

                        $product['product_status'] = 1;
                        $product['draft'] = 1;

                        Mst_store_product::create($product);
                        $id = DB::getPdo()->lastInsertId();

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
                        
                        
                        $sCount = 0;
                        if($request->product_type == 2)
                        {
                            $sCount = 1;
                        }


                            
                        $data3 = [
                                'product_id' => $id,
                                'store_id' => $store_id,
                                'variant_name' => $global_product->product_name,
                                'product_varient_price' => $global_product->regular_price,
                                'product_varient_offer_price' => $global_product->sale_price,
                                'product_varient_base_image' => $global_product->product_base_image,
                                'stock_count' => $sCount,
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
                                            'product_id' => @$id,
                                            'product_varient_id' => @$vari_id,
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
                            
                    }

                    $data['status'] = 1;
                    $data['message'] = "Product added to store.";
                    return response($data);
                        
                }
                else
                {
                    $data['status'] = 0;
                    $data['message'] = "Store not found ";
                    return response($data);
                }

        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }




    public function showReport(Request $request)
    {
        $data = array(); 
        try {
            
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                $dataRV = Trn_RecentlyVisitedProducts::select(
                    'trn__recently_visited_products.rvp_id',
                    'trn__recently_visited_products.visit_count',
                    'trn__recently_visited_products.created_at',
                    'trn__recently_visited_products.updated_at',
                    'trn_store_customers.customer_id',
                    'trn_store_customers.customer_first_name',
                    'trn_store_customers.customer_last_name',
                    'trn_store_customers.customer_mobile_number',
                    'mst_stores.store_id',
                    'mst_stores.store_name',
                    'mst_stores.store_mobile',
                    'mst_store_products.product_id',
                    'mst_store_products.product_code',
                    'mst_store_products.product_name',
                    'mst_store_products.product_brand',
                    'mst_store_product_varients.product_varient_id',
                    'mst_store_product_varients.variant_name',
                    'mst_store_agencies.agency_id',
                    'mst_store_agencies.agency_name',
                    'mst_store_categories.category_id',
                    'mst_store_categories.category_name',
                    'mst__sub_categories.sub_category_id',
                    'mst__sub_categories.sub_category_name'
                    )
                    ->join('trn_store_customers','trn_store_customers.customer_id','=','trn__recently_visited_products.customer_id')
                    ->join('mst_stores','mst_stores.store_id','=','trn__recently_visited_products.store_id')
                    ->join('mst_store_products','mst_store_products.product_id','=','trn__recently_visited_products.product_id')
                    ->join('mst_store_product_varients','mst_store_product_varients.product_varient_id','=','trn__recently_visited_products.product_varient_id')
                    ->leftJoin('mst_store_agencies','mst_store_agencies.agency_id','=','mst_store_products.vendor_id')
                    ->join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')
                    ->leftJoin('mst__sub_categories','mst__sub_categories.sub_category_id','=','mst_store_products.sub_category_id');
                   
                   

                            
                    $dataRV = $dataRV->where('mst_stores.store_id',$request->store_id);

                    if(isset($request->date_from))
                    {
                      $dataRV = $dataRV->whereDate('trn__recently_visited_products.created_at','>=',$request->date_from);
                    }
                    
                    if(isset($request->date_to))
                    {
                      $dataRV = $dataRV->whereDate('trn__recently_visited_products.created_at','<=',$request->date_to);
                    }
                    
                    if(isset($request->product_id))
                    {
                      $dataRV = $dataRV->where('trn__recently_visited_products.product_id','=',$request->product_id);
                    }
                    
                    if(isset($request->category_id))
                    {
                      $dataRV = $dataRV->where('mst_store_products.product_cat_id','=',$request->category_id);
                    }
                    
                    if(isset($request->sub_category_id))
                    {
                      $dataRV = $dataRV->where('mst_store_products.sub_category_id','=',$request->sub_category_id);
                    }
                    
                    if(isset($request->agency_id))
                    {
                      $dataRV = $dataRV->where('mst_store_products.vendor_id','=',$request->agency_id);
                    }
                 
                    if(isset($request->customer_id))
                    {
                      $dataRV = $dataRV->where('trn__recently_visited_products.customer_id',$request->customer_id);
                    }
                               
                    
                    $dataRV = $dataRV->orderBy('trn__recently_visited_products.rvp_id', 'DESC')
                    ->groupBy('trn__recently_visited_products.product_varient_id','trn__recently_visited_products.customer_id',DB::raw("DATE_FORMAT(trn__recently_visited_products.created_at, '%d-%m-%Y')"));

                    if(isset($request->page))
                    {
                        $dataRV = $dataRV->paginate(10, ['data'], 'page', $request->page);
                    }
                    else
                    {
                        $dataRV = $dataRV->paginate(10);
                    }
                    
               
                foreach($dataRV as $d)
                {
                    if(!isset($d->customer_last_name))
                    $d->customer_last_name = '';
                    if(!isset($d->customer_first_name))
                    $d->customer_first_name = '';
                    
                    $visitCount = Trn_RecentlyVisitedProducts::join('mst_store_product_varients','mst_store_product_varients.product_varient_id','=','trn__recently_visited_products.product_varient_id');
                    $visitCount =   $visitCount->where('trn__recently_visited_products.product_varient_id', $d->product_varient_id);
                    $visitCount =   $visitCount->where('trn__recently_visited_products.customer_id', $d->customer_id);
                    $visitCount =   $visitCount->count();
                    
                    $d->visit_count = $visitCount;
                    
                    $countInCart = Trn_Cart::where('customer_id', $d->customer_id);
                    $countInCart = $countInCart->where('product_varient_id', $d->product_varient_id);
                    $countInCart = $countInCart->sum('quantity'); 
                                                
                    $d->count_in_cart = $countInCart;
                    
                    $puchasedCount =  Trn_store_order::join('trn_order_items','trn_order_items.order_id','=','trn_store_orders.order_id');
                                              
                    $puchasedCount = $puchasedCount->where('trn_store_orders.customer_id', $d->customer_id);
                    $puchasedCount = $puchasedCount->where('trn_order_items.product_varient_id', $d->product_varient_id);
                    $puchasedCount = $puchasedCount->sum('trn_order_items.quantity');
                    
                    $d->purchased_count = $puchasedCount;
                }
                    
               
                    $data['recentVisitedProductReport'] = $dataRV;

                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
            }
            else
            {
                    $data['status'] = 0;
                    $data['message'] = "Store not found";
                    return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }
    
    
    
    public function showStoreVisitReport(Request $request)
    {
        $data = array(); 
        try {
            
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                $dataRVS = Trn_RecentlyVisitedStore::select(
                    'trn__recently_visited_stores.rvs_id',
                    'trn__recently_visited_stores.visit_count',
                    'trn__recently_visited_stores.created_at',
                    'trn__recently_visited_stores.updated_at',
                    'trn_store_customers.customer_id',
                    'trn_store_customers.customer_first_name',
                    'trn_store_customers.customer_last_name',
                    'trn_store_customers.customer_mobile_number',
                    'trn_store_customers.place',
                    'mst_stores.store_id',
                    'mst_stores.store_name',
                    'mst_stores.store_mobile',
                    'mst_towns.town_name'

                    );
                        $dataRVS =  $dataRVS->join('trn_store_customers','trn_store_customers.customer_id','=','trn__recently_visited_stores.customer_id');
                        $dataRVS =  $dataRVS->join('mst_stores','mst_stores.store_id','=','trn__recently_visited_stores.store_id');
                       $dataRVS = $dataRVS->join('mst_towns','mst_towns.town_id','=','mst_stores.town_id');
                       
                       $dataRVS = $dataRVS->where('mst_stores.store_id',$request->store_id);
                   
                    if(isset($request->date_from))
                    {
                      $dataRVS = $dataRVS->whereDate('trn__recently_visited_stores.created_at','>=',$request->date_from);
                    }
                    
                    if(isset($request->date_to))
                    {
                      $dataRVS = $dataRVS->whereDate('trn__recently_visited_stores.created_at','<=',$request->date_to);
                    }
                    
                    if(isset($request->customer_id))
                    {
                      $dataRVS = $dataRVS->where('trn_store_customers.customer_id','=',$request->customer_id);
                    }
                    
                    if(isset($request->town_id))
                    {
                      $dataRVS = $dataRVS->where('mst_stores.town_id','=',$request->town_id);
                    }
                   
                   
                   $dataRVS = $dataRVS->groupBy('trn__recently_visited_stores.store_id','trn__recently_visited_stores.customer_id',DB::raw("DATE_FORMAT(trn__recently_visited_stores.created_at, '%d-%m-%Y')"))
                    ->orderBy('trn__recently_visited_stores.rvs_id', 'DESC');
                    
                    
                    if(isset($request->page))
                    {
                        $dataRVS = $dataRVS->paginate(10, ['data'], 'page', $request->page);
                    }
                    else
                    {
                        $dataRVS = $dataRVS->paginate(10);
                    }
                    

                    $dataRVSs = array();
                    foreach($dataRVS as $d)
                    {
                        
                        if(!isset($d->customer_last_name))
                        $d->customer_last_name = '';
                        if(!isset($d->customer_first_name))
                        $d->customer_first_name = '';
                    
                        $visitCount = Trn_RecentlyVisitedStore::join('mst_stores','mst_stores.store_id','=','trn__recently_visited_stores.store_id');
                                               
                        $visitCount =   $visitCount->where('trn__recently_visited_stores.store_id', $d->store_id);
                        $visitCount =   $visitCount->where('trn__recently_visited_stores.customer_id', $d->customer_id);
                        $visitCount =   $visitCount->whereDate('trn__recently_visited_stores.created_at', \Carbon\Carbon::parse($d->created_at)->format('Y-m-d'));
                        $visitCount =   $visitCount->sum('trn__recently_visited_stores.visit_count');
                        $d->visit_count = $visitCount;
                        
                        
                        $puchasedCount =  new Trn_store_order;
                        $puchasedCount = $puchasedCount->join('mst_stores','mst_stores.store_id','=','trn_store_orders.store_id');
                        $puchasedCount = $puchasedCount->where('trn_store_orders.customer_id', $d->customer_id);
                        $puchasedCount = $puchasedCount->where('trn_store_orders.store_id', $d->store_id);
                        $puchasedCount = $puchasedCount->whereDate('trn_store_orders.created_at', \Carbon\Carbon::parse($d->created_at)->format('Y-m-d'));
                        $puchasedCount = $puchasedCount->count();
                        
                        $d->order_per_visit = $puchasedCount;
                        
                        // if(isset($request->visit_count) && !isset($request->order_per_visit))
                        // {
                        //     if($request->visit_count == $visitCount){
                        //         $dataRVSs[] = $d;
                        //     }
                        // }elseif(!isset($request->visit_count) && isset($request->order_per_visit))
                        // {
                        //   if($request->order_per_visit == $puchasedCount){
                        //         $dataRVSs[] = $d;
                        //     }
                            
                        // }elseif(isset($request->visit_count) && isset($request->order_per_visit))
                        // {
                        //     if(($request->visit_count == $visitCount) && ($request->order_per_visit == $puchasedCount)){
                        //         $dataRVSs[] = $d;
                        //     }
                        // }
                        // else
                        // {
                        //         $dataRVSs[] = $d;
                        // }

                    }
               
               
                    $data['recentVisitedStoreReport'] = $dataRVS;

                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
            }
            else
            {
                    $data['status'] = 0;
                    $data['message'] = "Store not found";
                    return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }
    
    
       public function listProductNames(Request $request)
    {
        $data = array(); 
        try {
            if(isset($request->store_id) && Mst_store::find($request->store_id))
            {
                $store_id = $request->store_id;
                if($data['productDetails']  = Mst_store_product::join('mst_store_categories','mst_store_categories.category_id','=','mst_store_products.product_cat_id')->where('mst_store_products.store_id',$store_id)->orderBy('mst_store_products.product_id', 'DESC')->select('mst_store_products.product_id','mst_store_products.product_name')->get())
                {
                    foreach($data['productDetails'] as $product){
                        $product->product_base_image = '/assets/uploads/products/base_product/base_image/'.$product->product_base_image;
                    }
                    $data['status'] = 1;
                    $data['message'] = "success";
                    return response($data);
                }
                else{
                    $data['status'] = 0;
                    $data['message'] = "failed";
                    return response($data);
                }
            }
            else
            {
                $data['status'] = 0;
                $data['message'] = "Store not found";
                return response($data);
            }
                
        }catch (\Exception $e) {
           $response = ['status' => '0', 'message' => $e->getMessage()];
           return response($response);
        }catch (\Throwable $e) {
            $response = ['status' => '0','message' => $e->getMessage()];
            return response($response);
        }
    }



   
    

}


