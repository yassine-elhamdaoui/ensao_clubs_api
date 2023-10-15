<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class RegistrationController
{
    private UserRepository $user;
    private UserPasswordHasherInterface $passwordHasher;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private SendMailService $mail;

    public function __construct(UserRepository $user, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator, SendMailService $mail)
    {
        $this->user = $user;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->mail = $mail;
    }

    public function __invoke(Request $request): User | JsonResponse
    {
        $user = new User();
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $emailExist = $this->user->findOneByEmail($email);

        if ($emailExist) {
            return new JsonResponse(["code" => JsonResponse::HTTP_CONFLICT, "message" => "Email already exists."], JsonResponse::HTTP_CONFLICT);
        }

        $user->setEmail($email);
        $user->setFirstName($request->request->get('firstName'));


        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setLastName($request->request->get('lastName'));
        $user->setImageFile($request->files->get('imageFile'));


        return $user;
    }

}
