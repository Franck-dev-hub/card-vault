<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserMappingTest extends KernelTestCase
{
    public function testMappingIsValid(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $validator = new SchemaValidator($entityManager);
        $classMetadata = $entityManager->getClassMetadata(User::class);

        $this->assertSame([], $validator->validateClass($classMetadata));
    }
}
