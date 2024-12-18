<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer 20 produits
        $products = array();
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('name' . $i);
            $product->setDescription('description' . $i);
            $product->setPhoto('photo' . $i . '.png');
            $product->setPrice($i);
            $manager->persist($product);
            array_push($products, $product);
        }

        // Créer 5 utilisateurs avec un panier
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setFirstname('firstname' . $i);
            $user->setLastname('lastName' . $i);
            $user->setEmail($user->getFirstname() . "." . $user->getLastname() . "@test.com");
            $user->setLogin('login' . $i);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'login' . $i));

            // Création du panier associé à l'utilisateur
            $cart = new Cart();
            $cart->setConfirmed(false);
            $cart->setUserId($user);
            $manager->persist($cart);

            $cart->addProduct($products[array_rand($products)]);
            $cart->addProduct($products[array_rand($products)]);
            $cart->addProduct($products[array_rand($products)]);
            $cart->addProduct($products[array_rand($products)]);
        }

        $manager->flush();
    }
}
