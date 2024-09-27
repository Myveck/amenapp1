import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/scss/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// // Sélectionner tous les liens dans la sidebar
// const links = document.querySelectorAll('.sidebar a');

// // Sélectionner toutes les sections
// const sections = document.querySelectorAll('section');

// // Fonction pour masquer toutes les sections
// function hideAllSections() {
//   sections.forEach(section => {
//     section.classList.remove('active');
//   });
// }

// function changeFocus() {
//     links.forEach(a => {
//         a.classList.remove('active')
//     })
// }

// // Fonction pour afficher la section sélectionnée
// function showSection(sectionId) {
//   const section = document.getElementById(sectionId);
//   section.classList.add('active');
// }

// // Ajout d'un événement "click" à chaque lien de la sidebar
// links.forEach(link => {
//   link.addEventListener('click', function(event) {
//     // event.preventDefault(); // Empêcher le comportement par défaut du lien
    
//     // Masquer toutes les sections
//       hideAllSections();
      
//       //   Enlever les éléments actifs
//       changeFocus();
      
//     //   Ajouter active
//       this.classList.add("active");

//     // Afficher la section correspondante
//     const sectionId = this.getAttribute('data-section');
//     showSection(sectionId);
//   });
// });

 const selectElement = document.getElementById('trie');

    // Ajouter un event listener pour détecter les changements
    selectElement.addEventListener('change', function() {
        // Parcourir toutes les options et réinitialiser l'attribut selected
        const options = selectElement.options;
        for (let i = 0; i < options.length; i++) {
            options[i].removeAttribute('selected'); // Supprimer l'attribut 'selected' s'il est présent
        }

        // Ajouter l'attribut 'selected' à l'option sélectionnée
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        selectedOption.setAttribute('selected', 'selected');
    });

