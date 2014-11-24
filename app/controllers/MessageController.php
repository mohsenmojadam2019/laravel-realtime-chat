<?php

class MessageController extends \BaseController {

	public function index() {

		$conversation = Conversation::where('name', Input::get('conversation'))->first();
		$messages 	  = Message::where('conversation_id', $conversation->id)->orderBy('created_at')->get();

		return View::make('templates/messages')->with('messages', $messages)->render();
	}

	public function store() {
		$rules 	   = array('body' => 'required');
		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			return Response::json([
				'success' => false,
				'result' => $validator->messages()
			]);
		}

		$conversation = Conversation::where('name', Input::get('conversation'))->first();

		$params = array(
			'conversation_id' => $conversation->id,
			'body' 			  => Input::get('body'),
			'user_id' 		  => Input::get('user_id'),
			'created_at'	  => new DateTime
		);

   		$message = Message::create($params);

   		// Create Message Notifications
   		$messages_notifications = array();

   		foreach($conversation->users() as $user_id) {
			array_push($messages_notifications, new MessageNotification(array('user_id' => $user_id, 'read' => false))); 
		}

		$message->messages_notifications()->saveMany($messages_notifications);

		// Publish Data To Redis
   		$data = array(
			'room' 	   => Input::get('conversation'), 
			'message'  => array( 'body' => Str::words($message->body, 5), 'user_id' => Input::get('user_id'))
		);

		Event::fire(ChatMessagesEventHandler::EVENT, array(json_encode($data)));

   		return Response::json([
   			'success' => true,
   			'result' => $message
   		]);
	}
}