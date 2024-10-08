import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/scss/app.css';


document.addEventListener('DOMContentLoaded', function() {
    const classeSelect = document.getElementById('classe');
    const matiereContainer = document.getElementById('matiere-container');

    if (classeSelect && matiereContainer) {
        classeSelect.addEventListener('change', function() {
            const classeId = this.value;

            fetch(`/examinations/matieres/${classeId}?_=${new Date().getTime()}`)
                .then(response => response.json())
                .then(matieres => {
                    matiereContainer.innerHTML = ''; // Clear previous options

                    if (matieres.length > 0) {
                        const matiereLabel = document.createElement('label');
                        matiereLabel.setAttribute('for', 'matiere-label');
                        matiereLabel.textContent = 'Matière';

                        const matiereSelect = document.createElement('select');
                        matiereSelect.name = 'matiere';
                        matiereSelect.id = 'matiere-label';
                        matiereSelect.classList.add('form-select', 'form-control');
                        matiereSelect.innerHTML = '<option value="">Choisir la matière</option>';

                        matieres.forEach(matiere => {
                            const option = document.createElement('option');
                            option.value = matiere.id;
                            option.text = matiere.nom;
                            matiereSelect.appendChild(option);
                        });

                        matiereContainer.appendChild(matiereLabel);
                        matiereContainer.appendChild(matiereSelect);
                    } else {
                        matiereContainer.textContent = 'Aucune matière disponible pour cette classe.';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la requête AJAX:', error);
                });
        });

        // Example of event delegation for handling changes on the dynamically created select
        matiereContainer.addEventListener('change', function(event) {
            if (event.target && event.target.id === 'matiere-label') {
                const selectedMatiere = event.target.value;
                console.log('Selected Matière:', selectedMatiere);

                // You can now handle the change event for dynamically added options
            }
        });
    }
});




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

    // public/js/genderChart.js

// Fonction pour initialiser le graphique avec les données transmises par le fichier Twig
function createGenderChart(boysCount, girlsCount) {
  const ctx = document.getElementById('genderChart').getContext('2d');
  const genderChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Garçons', 'Filles'],
      datasets: [{
        label: 'Répartition Garçons/Filles',
        data: [boysCount, girlsCount],
        backgroundColor: ['#4A6FA5', '#F9D1D1'],
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
        },
      },
    },
  });
}

document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[data-classe]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const classeId = this.getAttribute('data-classe');
            const url = 'eleves/' + classeId;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    const listeEleves = document.getElementById('listeEleves');
                    listeEleves.innerHTML = data.eleves.map(eleve => `
                        <div>
                            <span>${eleve.nom}</span>
                            <form action="${eleve.formAction}" method="get">
                                <select name="trimestre" class="form-control" onchange="this.form.submit()">
                                    <option value="">Imprimer</option>
                                    <option value="1">1er trimestre</option>
                                    <option value="2">2e trimestre</option>
                                    <option value="3">3e trimestre</option>
                                </select>
                            </form>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des élèves:', error);
                });
        });
    });
});





