# SISAG — Système de Suivi des Projets du Gouvernement

Bienvenue dans *SISAG*, une application web permettant de suivre, analyser et gérer les projets gouvernementaux à Kinshasa.  
Cette application est conçue pour offrir à la fois :

- une *interface citoyenne* pour consulter l’état des projets ;
- une *interface administrateur* pour gérer en profondeur la base des projets.

---

## Technologies utilisées

### Frontend
- *HTML5*
- *CSS3*
- *Bootstrap 5*
- *JavaScript*
- *Font Awesome*

### Backend
- *PHP*
- *MySQL (Wamp Server)*

---

## Fonctionnalités principales

### 1️*Page d’accueil*
Une page d'accueil moderne, responsive et intuitive présentant un aperçu global des projets.

*Capture d’écran :*  

![Page accueil](images/accueil.PNG)

---

### *Ajout automatique du statut d’un projet*
Lorsqu’un administrateur ajoute un projet :

| Condition | Statut automatique |
|----------|--------------------|
| Date du début > aujourd’hui | *À venir* |
| Date début ≤ aujourd’hui < date fin | *En cours* |
| Aujourd’hui ≥ date fin | *Terminé* |

L’avancement (progression) :
- Par défaut : *0%*
- Si projet terminé : *100% automatiquement*

(Capture de la page ajouter_projet.php)  
![Ajouter projet](images/ajouter_projet.PNG)

---

### 3️*Mise à jour intelligente (update.php)*

L’admin peut seulement modifier :
- le *statut*
- l’*avancement*

Règles de sécurité :

- Impossible de repasser :
  - d’un projet *terminé* → en cours / à venir  
  - d’un projet *en cours* → à venir  
  - d’un projet *en retard* → en cours  
- Si on met un projet *en cours*, la date d'aujourd’hui doit être ≥ date début  
- Si on met *terminé, l’avancement devient automatiquement **100%*

(Capture page update.php)  
![Update projet](images/update.PNG)

---

### *Changement automatique de statut*

Dans liste_projet_admin.php :

- Si un projet est *en cours*, mais la date de fin ≤ aujourd’hui :
  ✔ Statut change automatiquement en *en retard*  

(Capture tableau des projets — admin)  
![Liste projets admin](images/liste_admin.PNG)

---

### *Page des projets à venir*

Affiche tous les projets dont le statut = *à venir*.

Alerte automatique :
- Si la date du début ≤ aujourd’hui → suggère à admin de mettre le statut à jour.

(Capture projet_avenir.php)  
![Projets à venir](images/projets_avenir.PNG)

---

### *Page des projets critiques*

Un projet est critique si :
- son statut est *en retard*
- ou s’il dépasse sa date de fin

(Capture projet_critique.php)  
![Projets critiques](images/projets_critique.PNG)

---
### *Gestion commentaire*
Recupère les infos de celui qui commente et a la possibilité de :
- réjété son commentaire 
- ou l'approuvé
- 
  (Capture commentaire.php)  
![Projets critiques](images/commentaire.PNG)

### *Dashboard avancé*

Le tableau de bord affiche automatiquement :

#### Top 3 projets les plus avancés
Critères :
- avancement le plus élevé
- date de fin encore valide

#### Top 3 projets critiques
Critères :
- statut *en retard*
- date de fin dépassée

(Capture dashboard.php)  
![Dashboard](images/dashboard.PNG)

---

### *Responsive Design (Mobile & Desktop)*

- L’interface s’adapte aux petits écrans
- Sur mobile :  
  ✔ Le sidebar disparaît  
  ✔ Un bouton “menu” l’affiche au clic  

(Capture écran version mobile)  
![Mobile view](images/mobile.PNG)

---

### *Projets suivis*

Cette interface permet aux utilisateurs authentifiés d’accéder aux projets qu’ils ont sélectionnés pour suivi.  
Elle assure la consultation structurée des informations relatives à l’évolution, au statut et aux différentes mises à jour des projets.

![Mobile view](images/projets_suivis.PNG)

---

### *Module de gestion des citoyens*

Ce module constitue une interface administrative dédiée à la gestion des citoyens enregistrés dans le système.  
Il permet :

- La consultation des données personnelles enregistrées
- Le suivi des connexions et de l’activité des utilisateurs
- La mise à jour et la modification des informations si nécessaire
- La supervision globale des comptes utilisateurs

  ![Mobile view](images/gest_citoyen.PNG)



