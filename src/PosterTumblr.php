<?php


class PosterTumblr {

	private $client = null;
	private $blogName = null;

	public function __construct(
		$blogName
		, $consumerKey
		, $consumerSecret
		, $token
		, $tokenSecret
	) {
		$this->blogName = $blogName;
		$this->client = new Tumblr\API\Client($consumerKey, $consumerSecret);
		$this->client->setToken($token, $tokenSecret);
	}
	
	public function postPhoto($fileName)
	{
		$data = array(
			'type' => 'photo',
			'state' => 'draft',
			'data' => $fileName,
		);
		$this->client->createPost($this->blogName, $data);
	}

}
