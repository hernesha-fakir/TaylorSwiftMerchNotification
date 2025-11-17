<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'test@gmail.com',
            'password' => 'password',
            'remember' => true,
        ]);
    }
}
