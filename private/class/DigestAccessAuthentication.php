<?php
/**
 * Digest Access Authentication
 ***
 * Author: McTwist (9845)
 *
 * Handles Digest connections. All data is handled outside, but this makes sure
 * the connection is safe by checking verification values that are hashed.
 * It is made to be so 
 */

namespace Glass;

// Example
// // Create the digest
// $digest = new DigestAccessAuthentication('api.blocklandglass.com');
// // This is done whenever a connection without digest is retrieved
// $digest->generate();
// // Restore a previous session
// $digest->restore($nonce, $ident, $nonceCount);
// // Validate the user by testing with the password
// $data = $digest->validate($json, $method, $password);
// // Use these to get the information to store away
// $digest->getNonce();
// $digest->getOpaque();
// $digest->getNonceCount();

class DigestAccessAuthentication
{
	private $realm = '';
	private $qop = ['auth', 'auth-int'];
	private $nonce = null;
	private $nonceCount = null;
	private $opaque = null;

	// Initialize with realm
	function __construct($realm)
	{
		$this->realm = $realm;
	}

	// Generate a new authentication request
	function generate()
	{
		$this->nonce = self::generateSecure();
		$this->opaque = self::generateSecure();
		$this->nonceCount = 0;

		$obj = new \stdClass();

		// Realm
		$obj->realm = $this->realm;

		// QOP
		if (!empty($this->qop))
			$obj->qop = implode(',', $this->qop);

		// Nonce
		$obj->nonce = $this->nonce;

		// Opaque
		if ($this->opaque)
			$obj->opaque = $this->opaque;

		return $obj;
	}

	// Restore previous connection
	function restore($nonce, $opaque, $nonceCount)
	{
		$this->nonce = $nonce;
		$this->opaque = $opaque;
		$this->nonceCount = $nonceCount;
	}

	// Validate request
	// Returns the data if valid, null otherwise
	function validate($obj, $method, $password)
	{
		$sess = false;

		// Small checks
		if ($this->realm != $obj->realm)
			return null;
		if ($this->nonce != $obj->nonce)
			return null;
		if ($this->opaque != $obj->opaque)
			return null;
		// Require 
		if ((empty($this->qop) xor empty($obj->qop)) ||
			(!empty($this->qop) && !in_array($obj->qop, $this->qop)))
			return null;

		// Prepare session
		if (strpos($obj->algorithm, '-sess') >= 0)
		{
			$sess = true;
			$obj->algorithm = substr($obj->algorithm, 0, -5);
		}
		// Check if algorithm exists
		if (!in_array($obj->algorithm, hash_algos()))
		{
			return null;
		}

		$deli = ':';

		// HA1
		if ($sess)
		{
			$hash1 = hash($obj->algorithm, 
				implode($deli, 
					[$password, $obj->nonce, $obj->cnonce]));
		}
		else
		{
			$hash1 = $password;
		}

		// HA2
		if ($obj->qop == 'auth-int')
		{
			$hash2 = hash($obj->algorithm, 
				implode($deli, 
					[$method, $obj->uri, json_encode($obj->data, JSON_UNESCAPED_SLASHES)]));
		}
		else
		{
			$hash2 = hash($obj->algorithm, 
				implode($deli, 
					[$method, $obj->uri]));
		}

		// Response
		if ($obj->qop == 'auth' || $obj->qop == 'auth-int')
		{
			// Nonce counter
			$nc = intval($obj->nc);
			// Overflow
			if ($this->nonceCount == 0x7FFFFFFF && $nc == 1)
				$this->nonceCount = 0;
			if ($nc > $this->nonceCount)
				$this->nonceCount = $nc;
			else
				return null;

			$response = hash($obj->algorithm, 
				implode($deli, 
					[$hash1, $obj->nonce, $obj->nc, $obj->cnonce, $hash2]));
		}
		else
		{
			$response = hash($obj->algorithm, 
				implode($deli, 
					[$hash1, $obj->nonce, $hash2]));
		}

		return $obj->response == $response ? $obj->data : null;
	}

	// Get opaque
	function getOpaque()
	{
		return $this->opaque;
	}

	// Get nonce
	function getNonce()
	{
		return $this->nonce;
	}

	// Get nonce count
	function getNonceCount()
	{
		return $this->nonceCount;
	}

	// Hash a password
	// algo - The algorithm to use
	// realm - The realm connected to
	// username - The username used
	// password - The plain password
	static public function hashPassword($algo, $realm, $username, $password)
	{
		return hash($algo, implode(':', [strtolower($username), $realm, $password]));
	}

	// Generate a secure random
	static private function generateSecure()
	{
		return bin2hex(openssl_random_pseudo_bytes(32));
	}
}
