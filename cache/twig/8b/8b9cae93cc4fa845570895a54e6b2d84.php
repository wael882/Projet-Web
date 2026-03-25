<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* partials/header.html.twig */
class __TwigTemplate_4ef736b901f91fc5e3ce0c848b8b61c0 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!doctype html>
<html lang=\"fr\">
  <head>
    <meta charset=\"UTF-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
    <meta
      name=\"description\"
      content=\"Stagio - Votre tableau de bord étudiant.\"
    />
    <title>Stagio - Accueil</title>
  </head>
  <body>
    <header>
      <nav>
        <img src=\"image/logo.webp\" alt=\"Logo Stagio\" />
        <a href=\"";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "acceuil\">Accueil</a>
        <a href=\"";
        // line 17
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "rechercher\">Rechercher</a>
        <a href=\"";
        // line 18
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "favoris\">Favoris</a>
        <a href=\"";
        // line 19
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "candidature\">Mes candidatures</a>
        <!-- Nom de l'utilisateur connecté (sera dynamique en PHP) -->
        <span>Bonjour, Jean Dupont</span>
        <a href=\"";
        // line 22
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["bas_url"] ?? null), "html", null, true);
        yield "index\">Se déconnecter</a>
      </nav>
    </header>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/header.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  77 => 22,  71 => 19,  67 => 18,  63 => 17,  59 => 16,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/header.html.twig", "/mnt/c/projet ecole/Projet-Web-main/templates/partials/header.html.twig");
    }
}
