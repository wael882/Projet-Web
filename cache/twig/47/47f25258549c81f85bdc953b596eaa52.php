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

/* index.twig */
class __TwigTemplate_b027bee0c77fbe8c4dc8648ba1c607db extends Template
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
        return "base-index.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("base-index.html.twig", 1);
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
        yield "
    <main>
      <section id=\"hero\">
        <h1>Trouvez votre stage</h1>
        <p>
          Stagio est la plateforme officielle du CESI pour trouver votre stage.
          Parcourez les offres de nos entreprises partenaires, postulez en
          quelques clics et gérez vos candidatures depuis votre espace
          personnel.
        </p>
        <button>
          <a href=\"";
        // line 17
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "inscription\">S'inscrire</a>
        </button>
        <button>
          <a href=\"";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "identification\">S'identifier</a>
        </button>
      </section>

      <section id=\"chiffres\">
        <h2>Stagio en chiffres</h2>

        <div>
          <strong>+500</strong>
          <p>Offres de stage disponibles</p>
        </div>

        <div>
          <strong>+120</strong>
          <p>Entreprises partenaires</p>
        </div>

        <div>
          <strong>+1200</strong>
          <p>Étudiants inscrits</p>
        </div>

        <div>
          <strong>92%</strong>
          <p>Taux de placement en stage</p>
        </div>
      </section>

      <section id=\"comment-ca-marche\">
        <h2>Comment ça marche ?</h2>

        <div>
          <h3>1. Créez votre profil</h3>
          <p>Inscrivez-vous et complétez votre profil étudiant.</p>
        </div>

        <div>
          <h3>2. Parcourez les offres</h3>
          <p>
            Filtrez les offres par secteur, durée, localisation et type de
            contrat.
          </p>
        </div>

        <div>
          <h3>3. Postulez en un clic</h3>
          <p>Envoyez votre candidature directement depuis la plateforme.</p>
        </div>
      </section>

      <section id=\"offres\">
        <h2>Offres d'emploi</h2>

        <div class=\"card\">
          <img src=\"\" alt=\"Logo Thales\" />
          <h3>Thales</h3>
          <p>Cannes - 06</p>
          <p>Durée du stage : 4 mois</p>
          <a href=\"";
        // line 78
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "identification\">Voir l'offre</a>
          <!-- dois etre connecté pour acceder a l'ofre -->
        </div>

        <div class=\"card\">
          <img src=\"\" alt=\"Logo Aibus\" />
          <h3>Aibus</h3>
          <p>Paris - 94</p>
          <p>Durée du stage : 6 mois</p>
          <a href=\"";
        // line 87
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "identification\">Voir l'offre</a>
          <!-- dois etre connecté pour acceder a l'ofre -->
        </div>

        <div class=\"card\">
          <img src=\"\" alt=\"Logo Naval Group\" />
          <h3>Naval Group</h3>
          <p>Nantes - 44</p>
          <p>Durée du stage : 3 mois</p>
         <a href=\"";
        // line 96
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["base_url"] ?? null), "html", null, true);
        yield "identification\">Voir l'offre</a>
          <!-- dois etre connecté pour acceder a l'ofre -->
        </div>
      </section>
    </main>

";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "index.twig";
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
        return array (  174 => 96,  162 => 87,  150 => 78,  89 => 20,  83 => 17,  70 => 6,  63 => 5,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "index.twig", "/mnt/c/projet ecole/Projet-Web-main/templates/index.twig");
    }
}
