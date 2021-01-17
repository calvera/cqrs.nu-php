<?php

declare(strict_types=1);

namespace Cafe\Funcional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * todo: Since I'm constantly refactoring to code on Domain and Config and I'm still defining the architecture,
 * I will use these tests in order to help me on not breaking the app in this refactoring phase.
 */
class TabEverythingTest extends WebTestCase
{
    private int $tableNumber;
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->tableNumber = random_int(1000, 999999);
        $this->client = self::createClient();
    }

    /**
     * @test
     */
    public function can_open_tab(): int
    {
        $crawler = $this->client->request('GET', '/tab/open');

        $form = $crawler->selectButton('Open Tab')->form();
        $form['open_tab[tableNumber]'] = $this->tableNumber;
        $form['open_tab[waiter]'] = 'Anastasia';

        $this->client->submit($form);

        self::assertResponseRedirects('/tab/'.$this->tableNumber.'/order');

        return $this->tableNumber;
    }

    /**
     * @depends can_open_tab
     * @test
     */
    public function can_order_items(int $tableNumber): int
    {
        $crawler = $this->client->request('GET', '/tab/'.$tableNumber.'/order');

        $form = $crawler->selectButton('Place Order')->form();
        $form['order[items][1][numberToOrder]'] = 1;
        $form['order[items][14][numberToOrder]'] = 1;

        $this->client->submit($form);

        self::assertResponseRedirects('/tab/'.$tableNumber.'/status');

        return $tableNumber;
    }

    /**
     * @depends can_order_items
     * @test
     */
    public function can_see_tab_status(int $tableNumber): int
    {
        $this->client->request('GET', '/tab/'.$tableNumber.'/status');

        self::assertResponseIsSuccessful();

        return $tableNumber;
    }

    /**
     * @depends can_order_items
     * @test
     */
    public function can_mark_food_prepared(int $tableNumber): int
    {
        $crawler = $this->client->request('GET', '/chef');

        $form = $crawler->filter("form#form-${tableNumber}")->form();
        $form->setValues(['items' => [0 => 14]]);

        $this->client->submit($form);

        self::assertResponseRedirects('/chef');

        return $tableNumber;
    }

    /**
     * @depends can_mark_food_prepared
     * @test
     */
    public function can_serve_items(int $tableNumber): int
    {
        $crawler = $this->client->request('GET', '/tab/'.$tableNumber.'/status');

        $form = $crawler->selectButton('mark-served')->form();
        $form->setValues(
            [
                'items' => [
                    0 => 1,
                    1 => 14,
                ],
            ]
        );


        $this->client->submit($form);

        self::assertResponseRedirects('/tab/'.$tableNumber.'/status');

        return $tableNumber;
    }

    /**
     * @depends can_mark_food_prepared
     * @test
     */
    public function can_close_tab(int $tableNumber): void
    {
        $crawler = $this->client->request('GET', '/tab/'.$tableNumber.'/close');

        $form = $crawler->selectButton('Close Tab')->form();
        $form['close_tab[amountPaid]'] = 50;

        $this->client->submit($form);

        self::assertResponseRedirects('/');
    }
}