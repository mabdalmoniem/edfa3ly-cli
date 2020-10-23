<?php

namespace App\Commands;

use App\Models\Bill;
use App\Models\Cart;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class Checkout extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'createCart {--bill-currency=} {products*}';

    /**
     * @var Collection
     */

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates a bill for selected products';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currency = $this->option('bill-currency');
        $products = $this->argument('products');

        $cart = new Cart;
        $cart->collectValidProducts($products);

        if($cart->isEmpty()) {
            $this->error("Please enter a valid products! [T-shirt, Pants, Jacket or Shoes]");
            return;
        }

        $currency = Currency::getCurrency($currency);

        if(empty($currency)) {
            $this->error("Please enter a valid currency! [USD or EGP]");
            return;
        }

        $bill = new Bill($cart, $currency);
        $bill->convertCurrency()->applyTax()->applyOffers();

        $this->info("Subtotal: {$bill->getCurrency()->display($bill->getSubtotal())}");
        $this->info("Taxes: {$bill->getCurrency()->display($bill->getTax())}");
        if(count($bill->getDiscounts())) {
            $this->comment("Discounts:");
            foreach ($bill->getDiscounts() as $discount) {
                $this->comment("    {$discount['message']}");
            }
        }
        $this->info("Total: {$bill->getCurrency()->display($bill->getTotal())}");
    }
}
