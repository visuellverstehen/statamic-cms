<?php

namespace Statamic\Auth\Protect\Protectors\Password;

use Statamic\Exceptions\RedirectException;
use Statamic\Auth\Protect\Protectors\Protector;
use Facades\Statamic\Auth\Protect\Protectors\Password\Token;

class PasswordProtector extends Protector
{
    protected $siteWide = false;

    /**
     * Provide protection
     *
     * @return void
     */
    public function protect()
    {
        if (empty(array_get($this->config, 'allowed', []))) {
            abort(403);
        }

        if (! array_get($this->config, 'form_url')) {
            abort(403);
        }

        // if ($this->isPasswordFormUrl()) {
        //     return;
        // }

        if (! $this->hasEnteredValidPassword()) {
            $this->redirectToPasswordForm();
        }
    }

    public function hasEnteredValidPassword()
    {
        return (new Guard($this->scheme))->check(
            session("statamic:protect:password.passwords.{$this->scheme}")
        );
    }

    // protected function isPasswordFormUrl()
    // {
    //     return $this->url === $this->getPasswordFormUrl();
    // }

    protected function redirectToPasswordForm()
    {
        $url = $this->getPasswordFormUrl() . '?token=' . $this->generateToken();

        abort(redirect($url));
    }

    protected function getPasswordFormUrl()
    {
        return $this->config['form_url'];
    }

    protected function generateToken()
    {
        $token = Token::generate();

        session()->put("statamic:protect:password.tokens.$token", [
            'scheme' => $this->scheme,
            'url' => $this->url,
        ]);

        return $token;
    }
}
