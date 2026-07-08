# 🏥 RH+ — Système de Gestion des Ressources Humaines

![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![License](https://img.shields.io/badge/license-MIT-green)

RH+ est une plateforme web développée avec Laravel permettant la gestion centralisée des Ressources Humaines des Institutions Sanitaires Diocésaines (ISD).

L'application facilite la gestion du personnel, des congés, des absences, des demandes administratives, des stagiaires, des prestataires, des rapports RH et de l'administration générale.

---

# 📸 Aperçu (App en cours)

> *(Ajouter ici quelques captures d'écran du Dashboard, Personnel, Congés, Rapports, etc.)*

---

# ✨ Fonctionnalités

## 👥 Gestion du personnel

- Création d'un agent
- Modification
- Suppression logique
- Consultation détaillée
- Import Excel
- Export
- Recherche multicritère
- Gestion des photos

---

## 🩺 Gestion des congés

- Congé administratif
- Congé technique
- Congé maternité
- Calcul automatique :
  - jours ouvrables
  - jours acquis
  - ancienneté
  - jours restants
- Validation DRH
- Rejet avec motif
- Génération automatique du document officiel

---

## 🚑 Gestion des absences

- Création d'absence
- Validation DRH
- Absence déductible
- Calcul automatique des jours ouvrables
- Impact sur le solde des congés

---

## 📄 Gestion des demandes

Prise en charge de plusieurs types de demandes administratives :

- Permission
- Attestation
- Mutation
- Affectation
- Avance
- Disponibilité
- Mise à la retraite
- Autres demandes personnalisées

Chaque demande suit un workflow :

Brouillon

↓

Soumise

↓

Approuvée / Rejetée

---

## 👨‍⚕️ Gestion des stagiaires

- Convention
- Encadrement
- Période de stage
- Attestation
- Historique

---

## 🤝 Gestion des prestataires

- Création
- Suivi
- Contrats

---

## 📊 Rapports RH

Rapports filtrables selon :

- Service
- Corporation
- Contrat
- Sexe
- Statut

Export PDF dynamique contenant :

- Statistiques
- Personnel concerné
- Informations détaillées

---

## 📈 Tableau de bord

Dashboard spécifique selon le rôle :

- DRH
- Assistant RH
- Chef de service
- Surveillant
- Personnel
- Prestataire

Chaque utilisateur ne voit que les informations autorisées.

---

## 🔐 Gestion des rôles

Gestion des autorisations basée sur les rôles.

Exemples :

- DRH
- Assistant RH
- Chef Service
- Surveillant
- Personnel
- Prestataire

---

## 📑 Documents

Génération automatique :

- Décision de congé
- Attestation
- Rapports
- Documents administratifs

avec :

- Signature DRH
- Référence
- Logo
- En-tête institutionnel

---

# 🛠️ Technologies utilisées

- Laravel 13
- PHP 8.3
- MySQL
- Blade
- Bootstrap / CSS personnalisé
- Carbon
- DomPDF
- Maatwebsite Excel

---

# 📂 Structure du projet

```
app/
├── Http/
├── Models/
├── Services/
├── Policies/
├── Providers/

resources/
├── views/
├── css/
├── js/

routes/
database/
storage/
```

---

# ⚙️ Installation

```bash
git clone https://github.com/votre-compte/rh-plus.git

cd rh-plus

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan storage:link

php artisan serve
```

---

# 👨‍💻 Comptes utilisateurs

Le système propose plusieurs profils :

| Rôle | Accès |
|------|--------|
| DRH | Administration complète |
| Assistant RH | Gestion RH |
| Chef Service | Validation |
| Surveillant | Validation |
| Personnel | Consultation |
| Prestataire | Consultation |

---

# 📊 Architecture

Le projet utilise principalement :

- MVC Laravel
- Services
- Policies
- Middleware
- Form Request Validation
- Soft Deletes

---

# 📁 Principaux modules

```
Personnel
Congés
Absences
Demandes
Stagiaires
Prestataires
Rapports
Configuration RH
Historique
Tableau de bord
```

---

# 🚀 Fonctionnalités avancées

- Calcul automatique des congés
- Gestion des jours fériés
- Ancienneté ISD
- Calcul des jours ouvrables
- Import Excel
- Export PDF
- Signature électronique
- Historique des validations
- Notifications
- Recherche multicritère

---

# 📌 Évolutions prévues

- Notifications Email
- Tableau de bord analytique
- API REST
- Authentification 2FA
- Export Excel avancé
- Application mobile

---


# 👤 Auteur

**AZON Yélian Joanès**

Étudiant en Génie Logiciel — IFRI

Développeur Full Stack Laravel • React • Node.js

---

⭐ N'hésitez pas à mettre une étoile au projet si vous le trouvez utile.