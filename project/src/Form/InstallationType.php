<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class InstallationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
		//User info
            ->add('firstname', TextType::class, [
				"label"=>"Your first name",
				"required"=>true,
			])
			->add("lastname", TextType::class, [
				"label"=>"Your last name",
				"required"=>true,
			])
			->add("email", EmailType::class, [
				"label"=>"Your email address",
				"required"=>true,
			])
			->add("username", TextType::class, [
				"label"=>"Choose your username",
				"required"=>true,
				"help"=>"The username is used to connect to the website",
			])
			->add("password", RepeatedType::class, [
				"type"=>PasswordType::class,
				"invalid_message"=>"The passwords must match",
				"required"=>true,
				"first_options"=>[
					"label"=>"Enter a password",
				],
				"second_options"=>[
					"label"=>"Repeat password",
				],
			])
		//Category info
			->add("category", TextType::class, [
				"label"=>"First category name",
				"required"=>true,
			])
		//Tag info
			->add("tag", TextType::class, [
				"label"=>"First tag",
				"required"=>true,
			])
		//Website config
			->add("blog_name", TextType::class, [
				"label"=>"Website name",
				"required"=>true,
			])
			->add("blog_description", TextType::class, [
				"label"=>"Website description",
				"required"=>true,
			])
			->add("blog_email", EmailType::class, [
				"label"=>"Website email",
				"required"=>true,
				"help"=>"The email will be used for notices and error messages",
			])
			->add("blog_allowusers", CheckboxType::class, [
				"label"=>"Allow users registration",
				"required"=>false,
				"data"=>true,
			])
			->add("blog_validateusers", CheckboxType::class, [
				"label"=>"Admins have to validate users",
				"required"=>false,
				"data"=>false,
			])
			->add("blog_email_onregistration", CheckboxType::class, [
				"label"=>"Receive notices on new users",
				"required"=>false,
				"data"=>false,
			])
			->add("blog_allowcomments", CheckboxType::class, [
				"label"=>"Allow comments (global)",
				"required"=>false,
				"help"=>"You can also disallow comments for each article",
				"data"=>true,
			])
			->add("blog_allowanonymous", CheckboxType::class, [
				"label"=>"Allow anonymous comments",
				"required"=>false,
				"help"=>"Users won't need to register to post comments but will have to fill their name",
				"data"=>false,
			])
			->add("blog_validatecomments", CheckboxType::class, [
				"label"=>"Admins have to validate comments",
				"required"=>false,
				"data"=>false,
			])
			->add("blog_email_oncomment", CheckboxType::class, [
				"label"=>"Receive notices on comments",
				"required"=>false,
				"data"=>true,
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
