<?php

namespace Tymon\JWTAuth;

use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Audience;
use Tymon\JWTAuth\Claims\Subject;
use Tymon\JWTAuth\Claims\JwtId;

class PayloadFactory
{
    /**
     * @var \Tymon\JWTAuth\Claims\Factory
     */
    protected $claimFactory;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $ttl = 60;

    /**
     * @var array
     */
    protected $defaultClaims = ['iss', 'iat', 'exp', 'nbf'];

    /**
     * @var array
     */
    protected $claims = [];

    /**
     * @param \Tymon\JWTAuth\Claims\Factory  $claimFactory
     * @param \Illuminate\Http\Request  $request
     */
    public function __construct(Factory $claimFactory, Request $request)
    {
        $this->claimFactory = $claimFactory;
        $this->request = $request;
    }

    public function make()
    {
        $this->buildDefaultClaims();



        return new Payload($this->claims);
    }

    public function addClaim($name, $value)
    {
        $this->claims[$name] = $value;

        return $this;
    }

    protected function buildClaims()
    {

    }

    /**
     * Magically set the claims
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (class_exists($class = '\\Tymon\\JWTAuth\\Claims\\' . studly_case($method))) {
            $this->claims[] = new $class($parameters[0]);

            return $this;
        }

        throw new \BadMethodCallException("The Claim [$class] does not exist.");
    }






    // protected function buildDefaultClaims()
    // {
    //     return array_map([$this, 'buildClaim'], $this->defaultClaims);
    // }

    // protected function buildClaim($claim)
    // {
    //     if (method_exists($this, $claim))
    //     {
    //         $this->claims[] = $this->claimFactory->get($claim, $this->$claim());
    //     }

    //     throw new \Exception("[$claim] method not found");
    // }

    public function iss()
    {
        return $this->request->url();
    }

    public function iat()
    {
        return time();
    }

    public function exp()
    {
        return time() + ($this->ttl * 60);
    }

    public function nbf()
    {
        return time();
    }
}