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

/* acceuil.twig */
class __TwigTemplate_30c553797669ab7669acad44c30f6e97 extends Template
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

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("base.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "Stagio - Accueil";
        yield from [];
    }

    // line 5
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 6
        yield "    <main>
      <!-- Section de bienvenue personnalisée -->
      <section id=\"bienvenue\">
        <h1>Bonjour Jean 👋</h1>
        <p>Voici un résumé de votre activité sur Stagio.</p>
      </section>

      <!-- Résumé rapide de l'activité -->
      <section id=\"resume\">
        <h2>Votre activité</h2>

        <div class=\"stat-card\">
          <strong>3</strong>
          <p>Candidatures envoyées</p>
          <a href=\"";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "candidature\">Voir mes candidatures</a>
        </div>

        <div class=\"stat-card\">
          <strong>5</strong>
          <p>Offres en wishlist</p>
          <a href=\"";
        // line 26
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "favoris\">Voir mes favoris</a>
        </div>

        <div class=\"stat-card\">
          <strong>1</strong>
          <p>Réponse en attente</p>
        </div>
      </section>

      <!-- Dernières candidatures -->
      <section id=\"dernières-candidatures\">
        <h2>Mes dernières candidatures</h2>

        <article>
          <h3>Développeur Web — Thales</h3>
          <p>Envoyée le 01/03/2024</p>
          <p>Statut : <strong>En attente</strong></p>
          <a href=\"";
        // line 43
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "offre\">>Voir l'offre</a>
        </article>

        <article>
          <h3>Data Analyst — Airbus</h3>
          <p>Envoyée le 28/02/2024</p>
          <p>Statut : <strong>Refusée</strong></p>
          <a href=\"";
        // line 50
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "offre\">Voir l'offre</a>
        </article>

        <a href=\"";
        // line 53
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "candidature\">Voir toutes mes candidatures</a>
      </section>
    </main>

    <aside>
      <h2>Mon profil</h2>
      <p>Jean Dupont</p>
      <p>Formation : Informatique — Campus Paris</p>
      <p>Promotion : 2024</p>
      <a href=\"";
        // line 62
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "profil\">Modifier mon profil</a>
    </aside>

    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "acceuil.twig";
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
        return array (  143 => 62,  131 => 53,  125 => 50,  115 => 43,  95 => 26,  86 => 20,  70 => 6,  63 => 5,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "acceuil.twig", "/mnt/c/projet ecole/Projet-Web-main/templates/acceuil.twig");
    }
}
