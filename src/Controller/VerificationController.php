<?php 
namespace App\Controller;
// src/Controller/VerificationController.php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerificationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/verify/{token}", name="verify_email")
     */
    public function verifyEmail(string $token): Response
    {
        // Find the user by the verification token
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            // Handle invalid token (e.g., display an error message)
            return $this->render('verification/error.html.twig', ['message' => 'Invalid verification token']);
        }

        // Verify the user's email by removing the verification token
        $user->setVerificationToken(null);

        // Activate the user's account (you can customize this part)
        $user->setIsVerified(true);

        // Persist changes to the database
        $this->entityManager->flush();

        // Render a success template (you can customize this)
        return $this->render('verification/success.html.twig', ['message' => 'Email verified successfully']);
    }
}
