<?php

declare(strict_types=1);

namespace App\Tests\Web;

use App\Tests\Support\WebTester;

final class HomePageCest
{
    public function base(WebTester $I): void
    {
        $I->wantTo('home page works.');
        $I->amOnPage('/login');
        $I->fillField('username', 'admin');
        $I->fillField('password', 'admin123');
        $I->click('Masuk Aplikasi');
        $I->amOnPage('/');
        $I->see('Selamat Datang di TEQIC');
    }
}
