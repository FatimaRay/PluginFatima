<?php
// Vérifie si WordPress est chargé
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_submit', 'traiter_formulaire');
add_action('wp_ajax_nopriv_submit', 'traiter_formulaire');

function traiter_formulaire(){
    global $wpdb;

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'submit_form_nonce')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité.'));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){

         $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
         $statut = isset($_POST['statut']) ? sanitize_text_field($_POST['statut']) : '';
         $produit = isset($_POST['produit']) ? sanitize_text_field($_POST['produit']) : '';
         $nom = isset($_POST['nom']) ? sanitize_text_field($_POST['nom']) : '';
         $livraison = isset($_POST['livraison']) ? sanitize_text_field($_POST['livraison']) : '';
         $telephone= isset($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : '';
         
        $erreurs = [];
         $message_global = '';

        if (empty($email)) {
           $erreurs['email'] = "Ce champ est obligatoire";
        } 
        elseif (!is_email($email)) {
           $erreurs['email'] = "Cet e-mail n'est pas valide";
        }

        if (empty($statut)) {
         $erreurs['statut'] = "Ce champ est obligatoire";
        }

        if (empty($produit)) {
         $erreurs['produit'] = "Ce champ est obligatoire";
        }

         // Retourner les érreurs ou un messages global
        if (!empty($erreurs)) {
             wp_send_json(array(
                'erreurs' => $erreurs,
                'message_global' => 'Veuillez corriger les erreurs avant d\'envoyer ce formulaire.'
            ));
        }
        else{
            // Insertion des données
            $table_name = $wpdb->prefix . 'fg_entrees';
            $donnees = [
              'statut' => $statut,
               'nom' => $nom,
               'telephone' => $telephone,
               'email' => $email,
               'produit' => $produit,
               'livraison' => $livraison,
                'created_at' => current_time('mysql')
            ];
        }   
            // Insertion dans la base de données
            
            $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s');
            $insertion = $wpdb->insert($table_name, $donnees, $format);

            if($insertion){

                // Envoi au middleware
                $reponse = wp_remote_post('https://example.com/api', [
                  'body' => json_encode($donnees),
                  'headers' => ['Content-Type' => 'application/json']
                ]);
                if (is_wp_error($reponse)) {
                        error_log(print_r($reponse->get_error_message(), true));
                }
  
                // Envoi du prospect à la boite  email de goliat
                $to = 'info@goliat.fr';
                $subject = 'Nouveau prospect';
                $message = sprintf("Statut: %s\nNom: %s\nTéléphone: %s\nEmail: %s\nProduit: %s\nLieu de livraison: %s",
                                  $donnees['statut'], $donnees['nom'], $donnees['telephone'],  
                                  $donnees['email'], $donnees['produit'], $donnees['livraison']);
                             wp_mail($to, $subject, $message); 
                
                wp_send_json_success(array(
                     'message_global' => 'Formulaire soumis avec succès!',
                     'redirect_url' => home_url(' https://www.goliat.fr/validation-form/')
                ));
            } 
            else {
              wp_send_json_error(array('message' => 'Erreur lors de l\'insertion dans la base de données.'));
            }
            wp_die(); // Arrêter l'exécution pour AJAX
   }
}

   


