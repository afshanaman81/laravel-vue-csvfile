<?php
use Illuminate\Http\Request;
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

Route::get('/', function () {
    return view('welcome');
});

Route::post('file', function(Request $request){
	$file = $request->file('myFile')->store('csvfiles');
	$path = $request->file('myFile')->getRealPath();
	$csv_data = array_map('str_getcsv', file($path));
	
	//return back();
	//return back()->with(['csv_data' => $csv_data]);
	//return back()->with(compact('csv_data'));
   	//return view('import_fields', compact('csv_data'));
   	//return view('welcome', json_encode($csv_data));
   	return view('welcome', compact('csv_data'));
   	//return response()->json($csv_data);
   	//dd($csv_data);
});