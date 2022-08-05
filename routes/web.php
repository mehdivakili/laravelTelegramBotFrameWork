<?php
use \App\TelegramBot\TelegramBot;
use Illuminate\Support\Facades\Route;
use Spipu\Html2Pdf\Html2Pdf;
use \App\TelegramBot\BotRout;

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
    $a = view('welcome')->render();
    $html2pdf = new Html2Pdf();
    $html2pdf->writeHTML($a);
    $html2pdf->output('myPdf.pdf');
});/*
TelegramBot::Routes();
BotRout::only(['text'],'/^start$/',[\App\CommandController::class,'start']);
BotRout::any('/^reply photo$/',[\App\CommandController::class,'reply_photo']);
BotRout::except(['text'],'/^start (.+)/',[\App\CommandController::class,'start_with_arg']);
BotRout::any('/^view$/',[\App\CommandController::class,'view']);
BotRout::default([\App\CommandController::class,'default_func']);
BotRout::group(['status'=>1],function (){
    BotRout::any('/1/',[\App\CommandController::class,'only_status_1']);
    BotRout::default([\App\CommandController::class,'default_func_status_1']);
});*/


TelegramBot::Routes();
BotRout::any('/^start$/',[\App\CommandController::class,'start']);
BotRout::only(['photo'],'/download/',[\App\CommandController::class,'download']);
BotRout::any('/start (.+) (.+)/',[\App\CommandController::class,'start_with_arg']);
BotRout::default([\App\CommandController::class,'default_2']);
BotRout::default([\App\CommandController::class,'default_3'],3);
BotRout::callback('/hello/',[\App\CommandController::class,'start_callback']);
BotRout::any('/get route/',[\App\CommandController::class,'start_callback']);
BotRout::any('/poll/',[\App\CommandController::class,'poll']);
BotRout::any('/edit/',[\App\CommandController::class,'edited_message'],0,['edited_message', 'channel_post','message','edited_channel_post'],['join:@testcannal123789']);

