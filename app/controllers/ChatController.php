<?php

class ChatController extends \BaseController {
	
	public function index() {

		if(Input::has('conversation')) {
			$current_conversation = Conversation::where('name',  Input::get('conversation'))->firstOrFail();
		} else {
			$current_conversation = Auth::user()->conversations()->first();
		}

		if($current_conversation) {
			Session::set('current_conversation', $current_conversation->name);
		}

		$conversations = Auth::user()->conversations()->get();
		$users = User::where('id', '<>', Auth::user()->id)->get();
		$recipients = array();

		foreach($users as $key => $user) {
			$recipients[ $user->id] = $user->username;
		}

		return View::make('templates/chat')->with(array(
			'conversations' 	   => $conversations, 
			'current_conversation' => $current_conversation,
			'recipients' 		   => $recipients 
		));
	}
}