<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'prevent-back-history'],function(){


Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    return 'DONE'; //Return anything
});

Route::get('/', function () {
    return view('welcome');
})->name('first');

Route::get('/admin-dashboard', function () {
    return view('admin.dashboard');
});

Auth::routes();
Route::get('/qr', 'store\CouponController@makeQr');
Route::get('/geotest', 'store\CouponController@geotest');

Route::get('/transfer', 'store\StoreController@transfer');
Route::get('/store/terms-and-condition', 'PublicController@showTC');


// Super Admin login
Route::get('/home', 'HomeController@index')->name('home');

//Route::get('/admin-login', 'Auth\LoginController@store-login')->name('admin.login');
// Super Admin Forgot password
Route::get('/admin-login', 'Auth\LoginController@showLoginForm')->name('admin.login');

Route::get('get/invoice/{id}', 'PublicController@generatePdf')->name('share_invoice_pdf');
Route::get('item/list/{id}', 'PublicController@generateItemsPdf')->name('share_items_pdf');

//admin  password update

Route::get('admin-password/view', 'HomeController@changePassword')->name('admin.password');
Route::post('admin-password/update','HomeController@updatePassword')->name('admin.update_password');
Route::post('store-password/update/{store_id}','HomeController@updatePasswordStore')->name('admin.update_password_store');

// update profile
Route::get('admin-profile/view', 'HomeController@Profile')->name('admin.profile');
Route::post('admin-profile/update','HomeController@updateProfile')->name('admin.update_profile');

// Categories
Route::group(['namespace' => 'admin'],function(){
    
    Route::get('/send', 'MasterController@sendPushNotification');

    
   // feedback questions 
Route::get('admin/feedback-questions/list', 'MasterController@listFeedbackQuestion')->name('admin.list_feedback_questions');
Route::post('admin/feedback-question/store', 'MasterController@storeFeedbackQuestion')->name('admin.store_feedback_question');
Route::post('admin/feedback-question/update/{feedback_question_id}', 'MasterController@updateFeedbackQuestion')->name('admin.update_feedback_question');
Route::post('admin/feedback-questions/remove/{feedback_question_id}', 'MasterController@removeFeedbackQuestion')->name('admin.remove_feedback_questions');
   
  Route::post('/save-device-token', 'AdminController@saveDeviceToken')->name('save-token');

 
 Route::get('admin/feedback-questions/restore-list', 'MasterController@listResioreFeedbackQuestion')->name('admin.restore_list_feedback_question');
Route::post('admin/feedback-questions/restore/{feedback_question_id}', 'MasterController@restoreFeedbackQuestion')->name('admin.restore_feedback_question');
      
    // Super Admin Reports
    
    Route::get('admin/product-wise-report', 'ProductController@showReport')->name('admin.show_reports');
    Route::get('admin/product-visit-report', 'ProductController@showVisitReport')->name('admin.product_visit_reports');
    Route::get('admin/store-visit-report', 'ProductController@showStoreVisitReport')->name('admin.store_visit_reports');
    
    Route::get('admin/sales-report', 'ProductController@showSalesReport')->name('admin.sales_reports');
    Route::get('admin/online-sales-report', 'ProductController@showOnlineSalesReport')->name('admin.online_sales_reports');
    Route::get('admin/offline-sales-report', 'ProductController@showOfflineSalesReport')->name('admin.offline_sales_reports');
    Route::get('admin/delivery-report', 'ProductController@deliveryReport')->name('admin.delivery_reports');
    Route::get('admin/payment-report', 'ProductController@paymentReport')->name('admin.payment_reports');

     Route::get('admin/inventory-report', 'ProductController@showInventoryReport')->name('admin.inventory_reports');
    Route::get('admin/out-of-stock-report', 'ProductController@showOutofStockReport')->name('admin.out_of_stock_reports');
    Route::get('admin/referal-reports', 'ProductController@showReferalReport')->name('admin.referal_reports');

    Route::get('admin/store-name-list', 'ProductController@listStoreNames');
    Route::get('admin/town-name-list', 'ProductController@listTownNames');
    Route::get('admin/product-name-list', 'ProductController@listProductNames');
    Route::get('admin/sub-category-list', 'ProductController@listSubCategoryNames');

    

Route::get('admin/categories/list', 'SettingController@listCategory')->name('admin.list_category');
Route::get('admin/categories/create', 'SettingController@createCategory')->name('admin.create_category');
Route::post('admin/categories/store', 'SettingController@storeCategory')->name('admin.store_category');
Route::post('admin/categories/destroy/{category}', 'SettingController@destroyCategory')->name('admin.destroy_category');
Route::post('admin/categories/status/{category_id}', 'SettingController@statusCategory')->name('admin.status_category');
Route::get('admin/categories/edit/{id}', 'SettingController@editCategory')->name('admin.edit_category');
Route::post('admin/categories/update/{category_id}', 'SettingController@updateCategory')->name('admin.update_category');

Route::get('admin/category/restore-list', 'AdminController@listResioreCategory')->name('admin.restore_list_category');
Route::post('admin/category/restore/{category}', 'AdminController@restoreCategory')->name('admin.restore_category');

// Business Types

Route::get('admin/business_type/list', 'SettingController@listBusiness')->name('admin.list_business_type');
Route::get('admin/business_type/create', 'SettingController@createBusiness')->name('admin.create_business_type');
Route::post('admin/business_type/store', 'SettingController@storeBusiness')->name('admin.store_business_type');
Route::post('admin/business_type/destroy/{business_type}', 'SettingController@destroyBusiness')->name('admin.destroy_business_type');
Route::post('admin/business_type/status/{business_type_id}', 'SettingController@statusBusiness')->name('admin.status_business_type');
Route::get('admin/business_type/edit/{id}', 'SettingController@editBusiness')->name('admin.edit_business_type');
Route::post('admin/business_type/update/{business_type_id}', 'SettingController@updateBusiness')->name('admin.update_business_type');

Route::get('admin/business_type/restore-list', 'AdminController@listResioreBusiness')->name('admin.restore_list_business_type');
Route::post('admin/business_type/restore/{business_type}', 'AdminController@restoreBusiness')->name('admin.restore_business_type');
//AdminController
// Stores

Route::get('admin/store/list', 'SettingController@listStore')->name('admin.list_store');
Route::get('admin/store/create', 'SettingController@createStore')->name('admin.create_store');
Route::post('admin/store', 'SettingController@addStore')->name('admin.store');
Route::post('admin/store/destroy/{store}', 'SettingController@destroyStore')->name('admin.destroy_store');
Route::post('admin/store/status/{store_id}', 'SettingController@statusStore')->name('admin.status_store');
Route::get('admin/store/edit/{id}', 'SettingController@editStore')->name('admin.edit_store');
Route::post('admin/store/update/{store_id}', 'SettingController@updateStore')->name('admin.update_store');
Route::get('admin/store/view/{id}', 'SettingController@viewStore')->name('admin.view_store');
Route::post('admin/store/destroy/document/{document}', 'SettingController@destroyStore_Doc')->name('admin.destroy_store_doc');
Route::post('admin/store/destroy/link_delivery_boy/{link_delivery_boy}', 'SettingController@destroyAssignedDelivery_boy')->name('admin.store_link_delivery_boy');


Route::get('admin/store/restore-list', 'AdminController@listRestoreStore')->name('admin.restore_list_store');
Route::post('admin/store/restore/{store_id}', 'AdminController@restoreStore')->name('admin.restore_store');


// convert to global products
Route::post('admin/convert/to/global/{product_id}', 'ProductController@convertProduct')->name('admin.convert_to_global_product');
Route::get('admin/product/home-screen/{product_id}', 'ProductController@showInHome')->name('admin.show_in_home_screen');


Route::post('admin/store/destroy/image/{image}', 'SettingController@destroyStore_Image')->name('admin.destroy_store_image');

Route::post('admin/store/document_store', 'SettingController@Store_Doc')->name('admin.store_doc');
Route::post('admin/store/destroy/image_store', 'SettingController@Store_Image')->name('admin.store_image');

Route::get('admin/store/view_document/{id}', 'SettingController@viewDocument');
// assign store to subadmin
Route::get('admin/store/assign_subadmin/{id}', 'SettingController@assignSubadmin_store')->name('admin.list_assign_subadmin');
// remove assgned store
Route::get('admin/link/destroy/subadmin_store/{store_id}', 'SettingController@RemoveSubadminLink')->name('admin.remove_store_subadmin_link');

Route::post('admin/link/destroy{link_table_id}', 'SettingController@destroyLink')->name('admin.destroy_linked_store');

Route::post('admin/store/assign/subadmin/store', 'SettingController@addStoreSubadmin')->name('admin.add_store_subadmin');

// assign agency and delivery boy

Route::get('admin/link/destroy/delivery_boy_store/{link_id}', 'SettingController@RemoveDeliveryBoyStoreLink')->name('admin.remove_delivery_boy_subadmin_link');

Route::get('admin/link/destroy/agency_store/{link_id}', 'SettingController@RemoveAgencyStoreLink')->name('admin.remove_agency_subadmin_link');


Route::get('admin/store/assign_agency/{id}', 'SettingController@assignAgency')->name('admin.assign_agency_store');

Route::post('admin/store/assign/agency{store_id}', 'SettingController@addStoreAgency')->name('admin.add_store_agency');


Route::post('admin/store/destroy/agency/{agency}', 'SettingController@destroyStore_Agency')->name('admin.destroy_store_agency');


Route::get('admin/store/assign_delivery_boy/{id}', 'SettingController@assignDelivery_boy')->name('admin.assign_delivery_boy_store');

Route::post('admin/store/assign/delivery_boy{store_link_delivery_boy_id}', 'SettingController@addStoreDelivery_boy')->name('admin.add_store_delivery_boy');

Route::post('admin/store/destroy/delivery_boy/{delivery_boy}', 'SettingController@destroyStore_Delivery_boy')->name('admin.destroy_link_delivery_boy');

// agency

Route::get('admin/agency/list', 'SettingController@listAgency')->name('admin.list_agency');
Route::get('admin/agency/create', 'SettingController@createAgency')->name('admin.create_agency');
Route::post('admin/agency/store', 'SettingController@storeAgency')->name('admin.store_agency');
Route::get('admin/agency/edit/{id}', 'SettingController@editAgency')->name('admin.edit_agency');
Route::post('admin/agency/update/{agency_id}', 'SettingController@updateAgency')->name('admin.update_agency');
Route::post('admin/agency/status/{agency_id}', 'SettingController@statusAgency')->name('admin.status_agency');
Route::post('admin/agency/destroy/{agency}', 'SettingController@destroyAgency')->name('admin.destroy_agency');
Route::get('admin/agency/view/{id}', 'SettingController@viewAgency')->name('admin.view_agency');


Route::get('admin/agency/restore-list', 'AdminController@listRestoreAgency')->name('admin.restore_list_agency');
Route::post('admin/agency/restore/{agency_id}', 'AdminController@restoreAgency')->name('admin.restore_agency');

Route::post('admin/product/restore/{product_id}', 'AdminController@restoreProduct')->name('admin.restore_product');

// companies

Route::get('admin/company/list', 'SettingController@listCompany')->name('admin.list_company');
Route::get('admin/company/create', 'SettingController@createCompany')->name('admin.create_company');
Route::post('admin/company/store', 'SettingController@storecompany')->name('admin.store_company');
Route::get('admin/company/edit/{id}', 'SettingController@editCompany')->name('admin.edit_company');
Route::post('admin/company/update/{company_id}', 'SettingController@updateCompany')->name('admin.update_company');
Route::post('admin/company/status/{company_id}', 'SettingController@statusCompany')->name('admin.status_company');
Route::post('admin/company/destroy/{company}', 'SettingController@destroyCompany')->name('admin.destroy_company');
Route::get('admin/company/view/{id}', 'SettingController@viewCompany')->name('admin.view_company');



Route::get('admin/company/restore-list', 'AdminController@listRestoreCompany')->name('admin.restore_list_company');
Route::post('admin/company/restore/{company_id}', 'AdminController@restoreCompany')->name('admin.restore_company');

// customer


Route::get('admin/customer/list', 'SettingController@listCustomer')->name('admin.list_customer');

Route::get('admin/customer/edit/{id}', 'SettingController@editCustomer')->name('admin.edit_customer');
Route::post('admin/customer/update/{customer_id}', 'SettingController@updateCustomer')->name('admin.update_customer');
Route::post('admin/customer/status/{customer_id}', 'SettingController@statusCustomer')->name('admin.status_customer');
Route::post('admin/customer/otp_status/{customer_id}', 'SettingController@statusOTPCustomer')->name('admin.otp_status_customer');
Route::get('admin/customer/view/{id}', 'SettingController@viewCustomer')->name('admin.view_customer');
Route::post('admin/customer/destroy/{customer}', 'SettingController@destroyCustomer')->name('admin.destroy_customer');

Route::post('admin/customer/ajax/unique_email', 'SettingController@CheckCustomerEmail')->name('admin.unique_cus_email');
Route::post('admin/customer/ajax/unique_username', 'SettingController@CheckCustomerUsername')->name('admin.unique_cus_username');

//subadmin-super admin

 
Route::get('admin/subadmin/list', 'SettingController@listSubadmin')->name('admin.list_subadmin');
Route::get('admin/subadmin/create', 'SettingController@createSubadmin')->name('admin.create_subadmin');
Route::post('admin/subadmin/store', 'SettingController@storeSubadmin')->name('admin.store_subadmin');
Route::get('admin/subadmin/edit/{id}', 'SettingController@editSubadmin')->name('admin.edit_subadmin');
Route::post('admin/subadmin/update/{id}', 'SettingController@updateSubadmin')->name('admin.update_subadmin');
Route::post('admin/subadmin/destroy/{user}', 'SettingController@destroySubadmin')->name('admin.destroy_subadmin');


Route::get('admin/subadmin/restore-list', 'AdminController@listResioreSubadmin')->name('admin.restore_list_subadmin');
Route::post('admin/subadmin/restore/{id}', 'AdminController@restoreSubadmin')->name('admin.restore_subadmin');


//subadmin-sub admin dashboard

//Route::get('admin/store/subadmin/list', 'SettingController@listStoreSubadmin')->name('admin.list_store_subadmin');
Route::get('admin/store/subadmin/list', 'SettingController@listStore')->name('admin.list_store_subadmin');
Route::get('admin/store/edit/subadmin/{id}', 'SettingController@editStoreSubadmin')->name('admin.edit_store_subadmin');
Route::post('admin/store/update/subadmin/{store_id}', 'SettingController@updateStoreSubadmin')->name('admin.update_store_subadmin');
// agency
Route::get('admin/store/assign_agency/subsdmin/{id}', 'SettingController@assignSubadminAgency')->name('admin.assign_subadmin_agency_store');

Route::post('admin/store/assign/agency/subadmin/{store_id}', 'SettingController@addSubadminAgency')->name('admin.add_subadmin_agency');
// delivery boy
Route::get('admin/store/assign_delivery_boy/subadmin/{id}', 'SettingController@assignDelivery_boy_subadmin')->name('admin.assign_delivery_boy_store_subadmin');

Route::post('admin/store/assign/delivery_boy/subadmin{store_link_delivery_boy_id}', 'SettingController@addStoreDelivery_boy_subadmin')->name('admin.add_store_delivery_boy_subadmin');

Route::get('admin/store/view/subadmin/{id}', 'SettingController@viewStoreSubadmin')->name('admin.view_store_subadmin');

Route::post('admin/store/destroy/subadmin/{link_subadmin}', 'SettingController@destroyStoreSubadmin')->name('admin.destroy_store_subadmin');
Route::post('admin/store/status/subadmin/{store_link_subadmin_id}', 'SettingController@statusStoreSubadmin')->name('admin.status_store_subadmin');

// delivery boys


Route::get('admin/delivery_boy/list', 'SettingController@listDelivery_boy')->name('admin.list_delivery_boy');
Route::get('admin/delivery_boy/create', 'SettingController@createDelivery_boy')->name('admin.create_delivery_boy');
Route::post('admin/delivery_boy/store', 'SettingController@storeDelivery_boy')->name('admin.store_delivery_boy');
Route::get('admin/delivery_boy/edit/{id}', 'SettingController@editDelivery_boy')->name('admin.edit_delivery_boy');
Route::post('admin/delivery_boy/update/{delivery_boy_id}', 'SettingController@updateDelivery_boy')->name('admin.update_delivery_boy');
Route::post('admin/delivery_boy/destroy/{delivery_boy}', 'SettingController@destroyDelivery_boy')->name('admin.destroy_delivery_boy');

Route::get('admin/delivery_boy/view/{id}', 'SettingController@viewDelivery_boy')->name('admin.view_delivery_boy');

Route::get('admin/delivery_boy/assign_store/{id}', 'SettingController@AssignStore')->name('admin.assign_store');
Route::post('admin/delivery_boy/store/assign_store', 'SettingController@addAssignedStore')->name('admin.add_assign_store');
Route::post('admin/delivery_boy/status/{delivery_boy_id}', 'SettingController@changedBoyStatus')->name('admin.status_delivery_boy');

//b delivery boy asign to store
Route::post('admin/delivery_boys/store/assign_store', 'SettingController@addAssignedDeliveryBoy')->name('admin.assign_delivery_boy');

// order

Route::get('admin/order/list', 'SettingController@listOrder')->name('admin.list_order');
Route::get('admin/order/view/{id}', 'SettingController@viewOrder')->name('admin.view_order');
Route::get('admin/order/invoice/{id}', 'SettingController@viewInvoice')->name('admin.invoice_order');
Route::post('admin/order/status/{order_id}', 'SettingController@OrderStatus')->name('admin.status_order');
Route::post('admin/payment/status/{order_id}', 'SettingController@PayStatus')->name('admin.pay_status_order');
//store  payment settlment
Route::get('admin/payment_settlment/list', 'SettingController@list_store_payment_settlment')->name('admin.list_payment_settlment');
Route::post('admin/payment_settlment/commision_update/{settlment_id}', 'SettingController@updateCommision')->name('admin.status_store_payment');

Route::get('admin/payment_settlments/list', 'SettingController@list_stores_payment_settlment')->name('admin.list_payment_settlments');
Route::get('admin/stores/payment_settlment/list/{store_name}/{store_id}', 'SettingController@list_stores_payments')->name('admin.list_stores_payment_settlments');
Route::post('admin/store/pay/{store_id}', 'SettingController@pay_stores_payments')->name('admin.pay_stores_payment_settlments');

//subadmin paymentsY

Route::get('admin/subadmin/payment_settlments/list', 'SettingController@list_subadmin_payment_settlment')->name('admin.list_subadmin_payment_settlments');
Route::get('admin/subadmin/payment_settlment/list/{subadmin_name}/{subadmin_id}', 'SettingController@list_subadmin_payments')->name('admin.list_subadmin_payments');

Route::post('admin/subadmin/pay/{subadmin_id}', 'SettingController@pay_subadmin_payments')->name('admin.pay_subadmin_payments');

// delivery boy settlment

Route::get('admin/delivery_boy/payment_settlment/list', 'SettingController@list_delivery_payment_settlment')->name('admin.list_delivery_boy_payment_settlment');
Route::post('admin/payment_settlment/delivery_boy/commision_update/{delivery_boy_settlment_id}', 'SettingController@update_delivery_boy_Commision')->name('admin.status_delivery_boy_payment');
//new
Route::get('admin/delivery_boys/payment_settlment/list', 'SettingController@list_delivery_boys_payment_settlment')->name('admin.list_delivery_boys_payment_settlment');
Route::get('admin/delivery_boys/payment_settlment/list/{delivery_boy_name}/{delivery_boy_id}', 'SettingController@DeliveryBoyPaymentSettlment')->name('admin.list_delivery_boys_payments_settlment');
Route::post('admin/delivery_boy/pay/{delivery_boy_id}', 'SettingController@payDeliveryBoy')->name('admin.pay_delivery_boy');



//admin.list_disputes
Route::get('admin/disputes/list', 'SettingController@listDisputes')->name('admin.list_disputes');
Route::post('admin/disputes/status/{dispute_id}', 'SettingController@statusDisputes')->name('admin.dispute_status');

//admin.list issues

Route::get('admin/issues/list', 'AdminController@listIssues')->name('admin.list_issues');
Route::post('admin/issues/create', 'AdminController@createIssue')->name('admin.create_issue');
Route::post('admin/issues/remove/{issue_id}', 'AdminController@removeIssue')->name('admin.destroy_issue');
Route::post('admin/issues/update/{issue_id}', 'AdminController@updateIssue')->name('admin.update_issue');

 Route::get('admin/issues/restore-list', 'AdminController@listResioreIssues')->name('admin.restore_list_issues');
Route::post('admin/issues/restore/{issue_id}', 'AdminController@restoreIssues')->name('admin.restore_issues');



//payment settlement

Route::get('admin/payment/list', 'SettingController@listPayment')->name('admin.list_payment');

// order in subadmin
Route::get('admin/subadmin/order/list', 'SettingController@listSubadminOrder')->name('admin.list_subadmin_order');
// commision Calculation

Route::get('admin/commision/calculation', 'SettingController@AddtoCart')->name('admin.place_order');
Route::get('admin/checkout/order', 'SettingController@Delivery_boy_settlment')->name('admin.checkout_order');

// delivery boy order

Route::get('admin/delivery_boy/order/list', 'SettingController@listDeliveryboyOrder')->name('admin.list_delivery_boy_order');
// product

Route::get('admin/product/list', 'SettingController@listProduct')->name('admin.list_product');
Route::get('admin/product/create', 'SettingController@createProduct')->name('admin.create_product');
Route::post('admin/product/store', 'SettingController@storeProduct')->name('admin.store_product');

Route::get('admin/product/edit/{id}', 'SettingController@editProduct')->name('admin.edit_product');

Route::post('admin/product/update/{product_id}', 'SettingController@updateProduct')->name('admin.update_product');

Route::get('admin/product/view/{id}', 'SettingController@viewProduct')->name('admin.view_product');

Route::post('admin/product/destroy/{product}', 'SettingController@destroyProduct')->name('admin.destroy_product');

Route::post('admin/product/status/{product_id}', 'SettingController@statusProduct')->name('admin.status_product');
Route::post('admin/product/stock/update/{product_id}', 'SettingController@stockUpdate')->name('admin.stock_update_product');


Route::post('admin/product/attribute/destroy/{attr_groups}', 'SettingController@destroyAttribute')->name('admin.destroy_attribute');

Route::post('admin/product/attribute/store', 'SettingController@storeAttribute')->name('admin.store_attribute');

//product image
Route::post('admin/product_image/store/{product_id}','SettingController@store_product_image')->name('admin.store_product_image');

Route::post('admin/product_image/destroy/{product_image}','SettingController@destroy_product_image')->name('admin.destroy_product_image');

Route::post('admin/product_image/status/{product_image_id}', 'SettingController@status_product_image')->name('admin.status_product_image');


// ajax

Route::get('admin/product/ajax/get_category', 'SettingController@GetCategory');

Route::get('admin/product/ajax/get_subcategory', 'SettingController@GetSubCategory');
Route::get('admin/product/ajax/get_attr_value', 'SettingController@GetAttr_Value');


//attribute group

Route::get('admin/attribute_group/list','SettingController@listAttributeGroup')->name('admin.list_attribute_group');

Route::post('admin/attribute_group/store','SettingController@storeAttribute')->name('admin.store_attribute_group');

Route::get('admin/attribute_group/edit/{id}','SettingController@editAttributeGroup')->name('admin.edit_attribute_group');
Route::post('admin/attribute_group/update/{attr_group_id}','SettingController@updateAtrGroup')->name('admin.update_attribute_group');
Route::post('admin/attribute_group/destroy/{attribute_group}','SettingController@destroyAttr_Group')->name('admin.destroy_attribute_group');


Route::get('admin/attribute-group/restore-list', 'AdminController@listResioreAttrGroup')->name('admin.restore_list_attr_group');
Route::post('admin/attribute-group/restore/{attribute_group}', 'AdminController@restoreAttrGroup')->name('admin.restore_attr_group');


//admin/attribute-group/restore-list
//attribute value

Route::get('admin/attribute_value/list','SettingController@listAttr_Value')->name('admin.list_attribute_value');

Route::get('admin/attribute_value/create','SettingController@createAttr_Value')->name('admin.create_attribute_value');

Route::post('admin/attribute_value/store','SettingController@storeAttr_Value')->name('admin.store_attribute_value');

Route::get('admin/attribute_value/edit/{id}','SettingController@editAttr_Value')->name('admin.edit_attribute_value');

Route::post('admin/attribute_value/update/{attr_value_id}','SettingController@updateAttr_Value')->name('admin.update_attribute_value');

Route::post('admin/attribute_value/destroy/{attribute_value}','SettingController@destroyAttr_Value')->name('admin.destroy_attribute_value');

//reward transaction type
Route::get('admin/reward_transaction_type/list', 'SettingController@listRewardType')->name('admin.list_reward_transaction_type');

Route::get('admin/reward_transaction_type/create', 'SettingController@createRewardType')->name('admin.create_reward_transaction_type');
Route::post('admin/reward_transaction_type/store', 'SettingController@storeRewardType')->name('admin.store_reward_transaction_type');

Route::get('admin/reward_transaction_type/edit/{id}', 'SettingController@editRewardType')->name('admin.edit_reward_transaction_type');
Route::post('admin/reward_transaction_type/update/{transaction_type_id}', 'SettingController@updateRewardType')->name('admin.update_reward_transaction_type');

Route::post('admin/reward_transaction_type/destroy/{reward_type}', 'SettingController@destroyRewardType')->name('admin.destroy_reward_transaction_type');

// customer reward

Route::get('admin/customer_reward/list', 'SettingController@listCustomerReward')->name('admin.list_customer_reward');
Route::get('admin/add/reward-to-customer', 'MasterController@addRewardToCustomer')->name('admin.add_reward_to_customer');
Route::get('admin/add/reward-to-existing-customer', 'MasterController@addReward')->name('admin.add_rew_exis_cus');
Route::post('admin/store/reward-to-customer', 'MasterController@storeReward')->name('admin.store_po_exis_cus');
Route::post('admin/store/reward-to-existing-customer', 'MasterController@storeRewardToCustomer')->name('admin.store_points_to_customer');
Route::get('admin/list/reward-to-customer', 'MasterController@listRewardToCustomer')->name('admin.list_points_to_customer');

Route::post('admin/remove/reward-to-customer/{reward_to_customer_id}', 'MasterController@removeRewardToCustomer')->name('admin.remove_points_to_customer');
Route::post('admin/remove/temp/reward-to-customer/{reward_to_customer_temp_id}', 'MasterController@removeTempRewardToCustomer')->name('admin.remove_temp__points_to_customer');

Route::get('admin/edit/reward-to-customer/{reward_to_customer_id}', 'MasterController@editRewardToCustomer')->name('admin.edit_points_to_customer');
Route::get('admin/edit/temp/reward-to-customer/{reward_to_customer_temp_id}', 'MasterController@editTempRewardToCustomer')->name('admin.edit_temp_points_to_customer');

Route::post('admin/update/reward-to-customer/{reward_to_customer_id}', 'MasterController@updateRewardToCustomer')->name('admin.update_points_to_customer');
Route::post('admin/update/temp/reward-to-customer/{reward_to_customer_temp_id}', 'MasterController@updateTempRewardToCustomer')->name('admin.update_temp_points_to_customer');


//configure points
Route::get('admin/configure_points/list', 'SettingController@listConfigurePoints')->name('admin.list_configure_points');

// Route::post('admin/configure_points/status/{cp_id}', 'SettingController@statusConfigurePoints')->name('admin.status_configure_points');
// Route::get('admin/configure_points/create', 'SettingController@createConfigurePoints')->name('admin.create_configure_points');
Route::post('admin/configure_points/store/{cf_id}', 'SettingController@storeConfigurePoints')->name('admin.store_configure_points');
// Route::post('admin/configure_points/remove/{cp_id}', 'SettingController@removeConfigurePoints')->name('admin.destroy_configure_point');


// //registration points
// Route::get('admin/registration/list', 'SettingController@listRegistrationPoints')->name('admin.list_points_for_registration');
// Route::post('admin/registration/create', 'SettingController@createRegistrationPoints')->name('admin.create_registration_points');
// Route::post('admin/registration_points/status/{rp_id}', 'SettingController@statusRegistrationPoints')->name('admin.status_registration_points');
// Route::post('admin/registration_points/remove/{rp_id}', 'SettingController@removeRegistrationPoint')->name('admin.destroy_registration_point');


// //first_order points
// Route::get('admin/first_order/list', 'SettingController@listFirstOrderPoints')->name('admin.list_points_for_first_order');
// Route::post('admin/first_order/create', 'SettingController@createFirstOrderPoints')->name('admin.create_first_order_points');
// Route::post('admin/first_order/status/{fp_id}', 'SettingController@statusFirstOrderPoints')->name('admin.status_first_order_points');
// Route::post('admin/first_order/remove/{rp_id}', 'SettingController@removeFirstOrderPoint')->name('admin.destroy_first_order_point');


// //referal points
// Route::get('admin/points_for_referal/list', 'SettingController@listReferalPoints')->name('admin.list_points_for_referal');
// Route::post('admin/referal_points/create', 'SettingController@createReferalPoints')->name('admin.create_referal_points');
// Route::post('admin/referal_points/status/{rp_id}', 'SettingController@statusReferalPoints')->name('admin.status_referal_points');
// Route::post('admin/referal_points/remove/{rp_id}', 'SettingController@removeReferalPoints')->name('admin.destroy_status_referal_points');


// //rupee points
// Route::get('admin/rupee_points/list', 'SettingController@listPointsToRupee')->name('admin.list_points_to_rupee');
// Route::post('admin/rupee_points/create', 'SettingController@createPointsToRupee')->name('admin.create_points_to_rupee');
// Route::post('admin/rupee_points/status/{rp_id}', 'SettingController@statusRupeePoints')->name('admin.status_points');
// Route::post('admin/rupee_points/remove/{rp_id}', 'SettingController@RemoveRupeePoints')->name('admin.destroy_point_to_rupee');

// //point_redeem_limit points
// Route::get('admin/point_redeem_limit/list', 'SettingController@listPointsRedeemLimit')->name('admin.list_point_redeem_limit');
// Route::post('admin/points_redeemed/remove/{rp_id}', 'SettingController@RemovePointsRedeemed')->name('admin.destroy_points_redeemed');
// Route::post('admin/points_redeemed/create/', 'SettingController@createPointsRedeemed')->name('admin.create_points_redeemed');




Route::get('admin/configure_point/edit/{cp_id}', 'SettingController@editConfigurePoints')->name('admin.edit_configure_point');
Route::post('admin/configure_point/update/{cp_id}', 'SettingController@updateConfigurePoints')->name('admin.update_configure_points');

//default image ajax status

// Route::get('admin/ajax/set_default_image', 'AdminController@setDefaultImage')->name('admin.set_default_image');

Route::get('admin/ajax/set_default_image', 'AdminController@setDefaultImage');

Route::get('admin/ajax/change_default_image', 'AdminController@changeDefaultImage');




Route::get('admin/customer_reward/edit/{id}', 'SettingController@editCustomerReward')->name('admin.edit_customer_reward');
Route::post('admin/customer_reward/update/{reward_id}', 'SettingController@updateCustomerReward')->name('admin.update_customer_reward');

//districts master
Route::get('admin/districts/list', 'AdminController@listDistricts')->name('admin.list_districts');
Route::post('admin/districts/remove/{district_id}', 'AdminController@removeDistricts')->name('admin.destroy_district');
Route::post('admin/districts/create/', 'AdminController@createDistricts')->name('admin.create_district');
Route::post('admin/district/edit/{district_id}', 'AdminController@editDistricts')->name('admin.edit_district');
Route::get('admin/district/edit/{district_id}', 'AdminController@editDistrictsView')->name('admin.edit_district_view');

Route::get('admin/district/restore-list', 'AdminController@listRestoreDistricts')->name('admin.restore_list_districts');
Route::post('admin/districts/restore/{district_id}', 'AdminController@restoreDistricts')->name('admin.restore_district');


//town master
Route::get('admin/towns/list', 'AdminController@listTown')->name('admin.list_towns');
Route::post('admin/towns/remove/{town_id}', 'AdminController@removeTown')->name('admin.destroy_town');
Route::post('admin/towns/create/', 'AdminController@createTown')->name('admin.create_town');
Route::post('admin/town/edit/{town_id}', 'AdminController@editTown')->name('admin.edit_town');
Route::get('admin/town/edit/{town_id}', 'AdminController@editTownView')->name('admin.edit_town_view');

Route::get('admin/town/restore-list', 'AdminController@listRestoreTown')->name('admin.restore_list_town');
Route::post('admin/town/restore/{town_id}', 'AdminController@restoreTown')->name('admin.restore_town');

//vihicle types master
Route::get('admin/vihicle_types/list', 'AdminController@listVehicleTypes')->name('admin.list_vihicle_types');
Route::post('admin/vihicle_types/create', 'AdminController@createVehicleTypes')->name('admin.create_vehicle_type');
Route::post('admin/vihicle_types/remove/{vehicle_type_id}', 'AdminController@removeVehicleTypes')->name('admin.destroy_vehicle_type');
Route::post('admin/vihicle_types/update/{vehicle_type_id}', 'AdminController@updateVehicleTypes')->name('admin.update_vehicle_type');

Route::get('admin/vihicle-types/restore-list', 'AdminController@listRestoreVehicleTypes')->name('admin.restore_list_vehicle_type');
Route::post('admin/vihicle-types/restore/{vehicle_type_id}', 'AdminController@restoreVehicleTypes')->name('admin.restore_vehicle_type');

//Video 

Route::get('/admin/video/list', 'MasterController@listVideo')->name('admin.videos');
Route::get('/admin/video/create', 'MasterController@createVideo')->name('admin.create_video');
Route::post('/admin/video/store', 'MasterController@storeVideo')->name('admin.store_video');
Route::post('/admin/video/remove/{video_id}', 'MasterController@removeVideo')->name('admin.destroy_video');
Route::post('/admin/video/update/{video_id}', 'MasterController@updateVideo')->name('admin.update_video');
Route::get('/admin/video/edit/{video_id}', 'MasterController@editVideo')->name('admin.edit_video');

Route::get('/admin/video/restore-list', 'MasterController@listRestoreVideo')->name('admin.restore_list_videos');
Route::post('/admin/video/restore/{video_id}', 'MasterController@restoreVideo')->name('admin.restore_video');

//Global Product 

Route::get('/admin/global/products/list', 'ProductController@listGlobalProducts')->name('admin.global_products');
Route::get('/admin/global/product/create', 'ProductController@createGlobalProduct')->name('admin.create_global_product');
Route::post('/admin/global/product/store', 'ProductController@storeGlobalProduct')->name('admin.store_global_product');
Route::post('/admin/global/product/remove/{global_product_id}', 'ProductController@removeGlobalProduct')->name('admin.destroy_global_product');
Route::post('/admin/global/product/update/{global_product_id}', 'ProductController@updateGlobalProduct')->name('admin.update_global_product');
Route::get('/admin/global/product/edit/{global_product_id}', 'ProductController@editGlobalProduct')->name('admin.edit_global_product');
Route::get('admin/global/product/view/{global_product_id}', 'ProductController@viewGlobalProduct')->name('admin.edit_global_product');

Route::post('/admin/global/product/image/remove/{global_product_image_id}', 'ProductController@removeGlobalProductImage')->name('admin.destroy_global_product_image');

Route::get('/admin/global/product/import', 'ProductController@importGlobalProduct')->name('admin.import_global_product');
Route::post('/admin/global/product/post-import', 'ProductController@postImportGlobalProduct')->name('admin.store_imported_global_products');



Route::post('admin/global/product/video/remove/{global_product_video_id}', 'ProductController@removeGlobalProductVideo')->name('admin.destroy_global_video');
Route::get('admin/global/product/video/edit/{global_product_video_id}', 'ProductController@editGlobalProductVideo')->name('admin.edit_global_product_video');

Route::post('admin/global/product/video/update/{global_product_video_id}', 'ProductController@updateGlobalProductVideo')->name('admin.update_global_video');


Route::get('admin/global-product/restore-list', 'AdminController@listResioreGlobalProduct')->name('admin.restore_list_global_product');
Route::post('admin/global-product/restore/{global_product_id}', 'AdminController@restoreGlobalProduct')->name('admin.restore_global_product');


//Sub Category

Route::get('/admin/sub/category/list', 'MasterController@listSubCategory')->name('admin.sub_category');
Route::get('/admin/sub/category/create', 'MasterController@createSubCategory')->name('admin.create_sub_category');
Route::post('/admin/sub/category/store', 'MasterController@storeSubCategory')->name('admin.store_sub_category');
Route::post('/admin/sub/category/remove/{sub_category_id}', 'MasterController@removeSubCategory')->name('admin.destroy_sub_category');
Route::post('/admin/sub/category/update/{sub_category_id}', 'MasterController@updateSubCategory')->name('admin.update_sub_category');
Route::get('/admin/sub/category/edit/{sub_category_id}', 'MasterController@editSubCategory')->name('admin.edit_sub_category');
Route::post('admin/sub/category/status/{sub_category_id}', 'MasterController@statusSubCategory')->name('admin.status_sub_category');


Route::get('admin/sub-category/restore-list', 'AdminController@listResioreSubCategory')->name('admin.restore_list_sub_category');
Route::post('admin/sub-category/restore/{sub_category_id}', 'AdminController@restoreSubCategory')->name('admin.restore_sub_category');


// Route::get('admin/vihicle_types/create', 'AdminController@createVehicleTypes')->name('admin.create_vehicle_type');


//tax master
Route::get('admin/taex/list', 'AdminController@listTaxes')->name('admin.list_taxes');
Route::post('admin/tax/create', 'AdminController@createTax')->name('admin.create_tax');
Route::post('admin/tax/remove/{tax_id}', 'AdminController@removeTax')->name('admin.destroy_tax');
Route::post('admin/tax/update/{tax_id}', 'AdminController@updateTax')->name('admin.update_tax');
Route::get('admin/tax/edit/{tax_id}', 'AdminController@editTax')->name('admin.edit_tax');
Route::get('admin/tax/add', 'AdminController@addTaxes')->name('admin.add_taxes');

Route::get('admin/tax/restore-list', 'AdminController@listRestoreTaxes')->name('admin.restore_list_taxes');
Route::post('admin/tax/restore/{tax_id}', 'AdminController@restoreTax')->name('admin.restore_tax');



//BANNERS

//customer app banners
Route::get('admin/customer/app/banner/list', 'AdminController@listCustomerAppBanner')->name('admin.list_customer_app_banners');
Route::post('admin/customer/app/banner/create', 'AdminController@storeCustomerAppBanner')->name('admin.create_customr_app_banner');
Route::post('admin/customer/app/banner/remove/{banner_id}', 'AdminController@removeCustomerAppBanner')->name('admin.destroy_customer_app_banner');
Route::post('admin/customer/app/banner/status/{banner_id}', 'AdminController@statusCustomerBanner')->name('admin.status_customer_banner');


Route::get('admin/customer-app-banner/restore-list', 'AdminController@listRestoreCAB')->name('admin.restore_list_cab');
Route::post('admin/customer-app-banner/restore/{banner_id}', 'AdminController@restoreCAB')->name('admin.restore_cab');


//store app banner
Route::get('admin/store/app/banner/list', 'AdminController@listStoreAppBanner')->name('admin.list_store_app_banners');
Route::post('admin/store/app/banner/create', 'AdminController@storeStoreAppBanner')->name('admin.create_store_app_banner');
Route::post('admin/store/app/banner/remove/{banner_id}', 'AdminController@removeStoreAppBanner')->name('admin.destroy_store_app_banner');


Route::get('admin/store-app-banner/restore-list', 'AdminController@listRestoreSAB')->name('admin.restore_list_sab');
Route::post('admin/store-app-banner/restore/{banner_id}', 'AdminController@restoreSAB')->name('admin.restore_sab');

// ajax call
Route::get('admin/ajax/get_store', 'SettingController@GetStore');


Route::get('admin/ajax/get_state', 'SettingController@GetState');
Route::get('admin/ajax/get_city', 'SettingController@GetCity');
Route::get('admin/ajax/get_town', 'SettingController@GetTown');
Route::post('admin/store/ajax/unique_email', 'SettingController@CheckEmail')->name('admin.unique_email');

Route::post('admin/store/ajax/unique_username', 'SettingController@CheckUsername')->name('admin.unique_username');
// agency
Route::post('admin/agency/ajax/unique_email', 'SettingController@CheckAgencyEmail')->name('admin.unique_email_agency');
Route::post('admin/agency/ajax/unique_username', 'SettingController@CheckAgencyUsername')->name('admin.unique_username_agency');
Route::post('admin/agency/ajax/unique_email_company', 'SettingController@CheckCompanyEmail')->name('admin.unique_email_company');
Route::post('admin/agency/ajax/unique_username_company', 'SettingController@CheckCompanyUsername')->name('admin.unique_username_company');


Route::get('admin/update-terms', 'SettingController@updateTerms')->name('admin.edit_terms');
Route::post('admin/update-tc', 'SettingController@updateTC')->name('admin.update_tc');


});

Route::group(['namespace' => 'store'],function(){


Route::get('store/home', 'StoreController@index')->name('store_home');
Route::get('store-login', 'Auth\LoginController@showLoginForm')->name('store.login');
Route::post('store/login', 'Auth\LoginController@usrlogin')->name('store_login');
Route::post('store-logout', 'Auth\LoginController@logout')->name('store.logout');


// Store register page State Country Town Ajax

Route::get('store/register/ajax/get_state', 'Auth\RegisterController@GetState');
Route::get('store/register/ajax/get_city', 'Auth\RegisterController@GetCity');
Route::get('store/register/ajax/get_town', 'Auth\RegisterController@GetTown');
Route::post('admin/stores/ajax/unique_email', 'Auth\RegisterController@CheckEmail')->name('store.unique_email');

//ajax mobile number
Route::post('admin/stores/ajax/unique_store_mobile', 'Auth\RegisterController@CheckMobile')->name('store.unique_store_mobile');


// registration & otp verification

Route::get('store-registration','Auth\RegisterController@showRegistrationForm')->name('show_register.store');

Route::post('store/registration','Auth\RegisterController@storeRegistration')->name('register.store');

Route::get('store/registration/otp_verify/view/{id}','Auth\RegisterController@otpVerificationview')->name('otp_verify_view.store');

Route::post('store/registration/otp_verify/{store_id}','Auth\RegisterController@otpVerification')->name('otp_verify.store');

Route::get('store/registration/otp_verify/resend_otp/{store_id}','Auth\RegisterController@ResendOTP')->name('resend_otp.store');

    Route::post('store/otp-verification-status/save', 'Auth\RegisterController@saveOVS')->name('saveOVS');



Route::get('store-password/request', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('store_password.request');

// forget password otp
Route::get('store/forgot/password/resend_otp/{store_id}','Auth\ForgotPasswordController@ResendOTP')->name('resend_forgot_pass_otp.store');
Route::post('store/forgot/password/otp_verify/{store_id}','Auth\ForgotPasswordController@otpVerification')->name('forgot_password_otp_verify.store');
Route::get('change/store-password/{store_id}','Auth\ForgotPasswordController@changePassword')->name('resend_password_otp.store');
Route::post('change-store-password/{store_id}','Auth\ForgotPasswordController@resetPassword')->name('change-store-password');


// mobile otp forgot password

Route::get('store-password/store-mobile', 'Auth\ForgotPasswordController@sendResetOTP')->name('store_password.store_mobile');
Route::post('store-password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('store_password.email');

Route::get('store-password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('store_password.reset');
Route::post('store-password/reset', 'Auth\ResetPasswordController@reset')->name('store_password.update');
Route::get('store/product/ajax/global_product', 'StoreController@GetGlobal_Product');


// product
Route::get('store/product/list', 'StoreController@listProduct')->name('store.list_product');

Route::get('store/product/create', 'StoreController@createProduct')->name('store.create_product');

Route::post('store/product/store', 'StoreController@storeProduct')->name('store.store_product');

Route::get('store/product/edit/{id}', 'StoreController@editProduct')->name('store.edit_product');
Route::get('store/ajax/product/set_default_image', 'StoreController@setDefaultImage');
Route::get('product/ajax/is-code-available', 'CouponController@isPCodeAvailable');

Route::post('store/product/update/{product_id}', 'StoreController@updateProduct')->name('store.update_product');

Route::get('store/product/view/{id}', 'StoreController@viewProduct')->name('store.view_product');

Route::post('store/product/destroy/{product}', 'StoreController@destroyProduct')->name('store.destroy_product');
Route::post('store/product/destroy/image/{product_image_id}', 'StoreController@destroyProductImage')->name('store.destroy_product_image');

Route::post('store/product/status/{product_id}', 'StoreController@statusProduct')->name('store.status_product');
Route::post('store/product/stock/update/{product_id}', 'StoreController@stockUpdate')->name('store.stock_update_product');

Route::post('store/product/attribute/destroy/{attr_groups}', 'StoreController@destroyAttribute')->name('store.destroy_attribute');
Route::post('store/product/attribute/store', 'StoreController@storeAttribute')->name('store.store_attribute');


Route::post('store/product/variant/destroy/{product_varient_id}', 'StoreController@destroyProductVariant')->name('store.destroy_product_variant');
Route::post('store/product/variant/attr/destroy/{variant_attribute_id}', 'StoreController@destroyProductVariantAttr')->name('store.destroy_product_var_attr');
Route::post('store/product/variant/attr/add', 'StoreController@addProductVariantAttr')->name('store.add_attr_to_variant');
Route::get('store/product/variant/list/{product_id}', 'StoreController@listProductVariant');

Route::get('store/product/variant/edit/{product_varient_id}', 'StoreController@editProductVariant');
Route::post('store/product/variant/update/{product_varient_id}', 'StoreController@updateProductVariant')->name('store.update_product_variant');


// get parent cat and sub cat by ajax

Route::get('store/product/ajax/get_category', 'StoreController@GetCategory');

Route::get('store/product/ajax/get_subcategory', 'StoreController@GetSubCategory');
Route::get('store/product/ajax/get_attr_value', 'StoreController@GetAttr_Value');
Route::get('store/ajax/find-availble-dboys', 'StoreController@GetAvailableDBoy');

// store password update

Route::get('store-password/view', 'StoreController@changePassword')->name('store.password');
Route::post('store-password/update','StoreController@updatePassword')->name('store.update_password');

// inventory management

Route::get('store/inventory/list', 'StoreController@listInventory')->name('store.list_inventory');
Route::post('store/stock/update/ajax', 'StoreController@UpdateStock')->name('store.stock_update');
Route::post('store/stock/reset/ajax', 'StoreController@resetStock')->name('store.stock_reset');

// pos
Route::get('store/pos/list', 'StoreController@listPOS')->name('store.list_pos');
Route::post('store/pos/save', 'StoreController@savePOS')->name('store.save_pos');
Route::post('stores/ajax/find/product', 'StoreController@findProduct')->name('store.find_product');
Route::post('stores/ajax/find/customer', 'StoreController@findCustomer')->name('store.find_customer');
Route::post('stores/ajax/find/tax', 'StoreController@findTax')->name('store.find_tax'); // not implimented
Route::post('stores/ajax/find/product-avilability', 'StoreController@checkProductAvailability')->name('store.checkProductAvailability');

// update profile

Route::get('store-profile/view', 'StoreController@Profile')->name('store.profile');
Route::post('store-profile/update','StoreController@updateProfile')->name('store.update_profile');
Route::post('store/destroy/document/{document}', 'StoreController@destroyStore_Doc')->name('store.destroy_store_doc');
Route::post('store/destroy/image/{image}', 'StoreController@destroyStore_Image')->name('store.destroy_store_image');

// agency
Route::get('store/agency/list', 'StoreController@listAgency')->name('store.list_agency');

Route::get('store/agency/create', 'StoreController@createAgency')->name('store.create_agency');

Route::post('store/agency/store', 'StoreController@storeAgency')->name('store.store_agency');


Route::get('store/agency/assign_agency', 'StoreController@AssignAgency')->name('store.assign_agency');
Route::post('store/agency/assign_agency/store', 'StoreController@storeAssignAgency')->name('store.store_assign_agency');
// ajax call for add agency

Route::get('store/ajax/get_state', 'StoreController@GetState');
Route::get('store/ajax/get_city', 'StoreController@GetCity');
Route::get('store/ajax/get_town', 'StoreController@GetTown');

Route::post('store/agency/ajax/unique_email', 'StoreController@CheckAgencyEmail')->name('store.unique_email_agency');
Route::post('store/agency/ajax/unique_username', 'StoreController@CheckAgencyUsername')->name('store.unique_username_agency');

//store settings
Route::get('store/settings', 'StoreController@settings')->name('store.settings');
Route::post('store/settings/update', 'StoreController@updateStoreSettings')->name('store.update_store_settings');


//store_admins

Route::get('store/admin/list', 'StoreController@listStoreAdmin')->name('store.store_admin');
Route::get('store/admin/create', 'StoreController@createStoreAdmin')->name('store.create_store_admin');
Route::post('store/admin/store', 'StoreController@storeStoreAdmin')->name('store.store_store_admin');
Route::post('store/admin/remove/{store_admin_id}', 'StoreController@removeStoreAdmin')->name('store.destroy_store_admin');
Route::post('store/admin-data/update/{store_admin_id}', 'StoreController@updateStoreAdmin')->name('store.update_store_admin');
Route::get('store/admin/edit/{store_admin_id}', 'StoreController@editStoreAdmin')->name('store.edit_store_admin');


// store payments

Route::get('store/payment_settlments', 'StoreController@storePayments');


// time_slots
Route::get('store/time/slot', 'StoreController@time_slot')->name('store.time_slots');
Route::post('store/time/slot/update', 'StoreController@updateTimeSlot')->name('store.update_time_slot');

Route::get('store/delivery/time/slot', 'StoreController@delivery_time_slots')->name('store.delivery_time_slots');
Route::post('store/delivery/time/slot/update', 'StoreController@update_delivery_time_slots')->name('store.update_delivery_time_slots');


//Global Product 

Route::get('/store/global/products/list', 'StoreController@listGlobalProducts')->name('store.global_products');
// add to global product
Route::get('/store/add/global/products/{global_product_id}', 'StoreController@convertGlobalProducts')->name('store.global_product_add_to_store');
Route::get('store/global/product/view/{global_product_id}', 'StoreController@viewGlobalProduct')->name('admin.edit_global_product');
Route::post('/store-to-global/products/list', 'StoreController@storeToGlobalProducts')->name('store.global_to_store_products');




// order Managemnet

Route::get('store/order/list', 'StoreController@listOrder')->name('store.list_order');
Route::get('store/today-order/list', 'StoreController@listTodaysOrder')->name('store.list_todays_order');
Route::get('store/order/view/{id}', 'StoreController@viewOrder')->name('store.view_order');
Route::post('store/order/update/{id}', 'StoreController@updateOrder')->name('store.update_order');
Route::get('store/order/invoice/{id}', 'StoreController@viewInvoice')->name('store.invoice_order');
Route::post('store/order/status/{order_id}', 'StoreController@OrderStatus')->name('store.order_status');

Route::get('store/share-items', 'StoreController@ShareItems')->name('store.share_item_list');

//invoice 

Route::get('store/product_invoice/pdf/{id}', 'StoreController@generatePdf')->name('store.generate_invoice_pdf');
Route::get('store/product_invoice/whatsup/send/{id}', 'StoreController@SendInvoice')->name('store.send_invoice');


Route::get('store/disputes/list', 'StoreController@listDisputes')->name('store.list_disputes');
Route::post('store/disputes/status/{dispute_id}', 'StoreController@statusDisputes')->name('store.dispute_status');

Route::get('store/current-issues', 'StoreController@currentIssues')->name('store.current_issues');
Route::get('store/new-issues', 'StoreController@newIssues')->name('store.new_issues');

// coupon

Route::get('store/coupon/list', 'CouponController@listCoupon')->name('store.list_coupon');
Route::get('store/coupon/create', 'CouponController@createCoupon')->name('store.create_coupon');
Route::post('store/coupon/store', 'CouponController@storecoupon')->name('store.store_coupon');
Route::post('store/coupon/remove/{coupon_id}', 'CouponController@removecoupon')->name('store.destroy_coupon');
Route::post('store/coupon/update/{coupon_id}', 'CouponController@updatecoupon')->name('store.update_coupon');
Route::get('store/coupon/edit/{coupon_id}', 'CouponController@editcoupon')->name('store.edit_coupon');



// assign order to delivery boy

Route::get('store/assign_order/delivery_boy/{id}', 'StoreController@AssignOrder')->name('store.assign_order');
Route::post('store/assign_order/delivery_boy/{order_id}', 'StoreController@storeAssignedOrder')->name('store.assign_store_order');


//attribute group

Route::get('store/attribute_group/list','StoreController@listAttributeGroup')->name('store.list_attribute_group');

Route::post('store/attribute_group/store','StoreController@storeAttribute')->name('store.store_attribute_group');

Route::get('store/attribute_group/edit/{id}','StoreController@editAttributeGroup')->name('store.edit_attribute_group');
Route::post('store/attribute_group/update/{attr_group_id}','StoreController@updateAtrGroup')->name('store.update_attribute_group');
Route::post('store/attribute_group/destroy/{attribute_group}','StoreController@destroyAttr_Group')->name('store.destroy_attribute_group');

//attribute value

Route::get('store/attribute_value/list','StoreController@listAttr_Value')->name('store.list_attribute_value');

Route::get('store/attribute_value/create','StoreController@createAttr_Value')->name('store.create_attribute_value');

Route::post('store/attribute_value/store','StoreController@storeAttr_Value')->name('store.store_attribute_value');

Route::get('store/attribute_value/edit/{id}','StoreController@editAttr_Value')->name('store.edit_attribute_value');

Route::post('store/attribute_value/update/{attr_value_id}','StoreController@updateAttr_Value')->name('store.update_attribute_value');

Route::post('store/attribute_value/destroy/{attribute_value}','StoreController@destroyAttr_Value')->name('store.destroy_attribute_value');


//store delivery boys
Route::get('store/delivery-boys/list','StoreController@listDeliveryBoys')->name('store.list_boys');
Route::get('store/delivery-boys/location/{delivery_boy_id}','StoreController@showLocation')->name('store.delivery_boy_location');

Route::get('store/delivery-order/view/{id}', 'StoreController@viewDeliveryOrder')->name('store.view_delivery_order');

Route::get('store/switch=status','StoreController@switchStatus')->name('store.switchStatus');

// video gallery
Route::get('store/video-gallery','StoreController@videoGallery')->name('store.video_gallery');


// store Report
    
    Route::get('store/product-wise-report', 'CouponController@showReport')->name('store.show_reports');
    Route::get('store/store-visit-report', 'CouponController@showStoreVisitReport')->name('store.store_visit_reports');
    Route::get('store/town-name-list', 'CouponController@listTownNames');
    Route::get('store/sales-report', 'CouponController@showSalesReport')->name('store.sales_reports');
    Route::get('store/inventory-report', 'CouponController@showInventoryReport')->name('store.inventory_reports');
    Route::get('store/out-of-stock-report', 'CouponController@showOutofStockReport')->name('store.out_of_stock_reports');

    Route::get('store/delivery-report', 'CouponController@deliveryReport')->name('store.delivery_reports');
    Route::get('store/payment-report', 'CouponController@paymentReport')->name('store.payment_reports');


    Route::get('store/online-sales-report', 'CouponController@showOnlineSalesReport')->name('store.online_sales_reports');
    Route::get('store/offline-sales-report', 'CouponController@showOfflineSalesReport')->name('store.offline_sales_reports');

    Route::post('store/browser-token/save', 'CouponController@saveBrowserToken')->name('store.saveBrowserToken');


});

});
