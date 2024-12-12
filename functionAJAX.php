<?php
// Vérifie si WordPress est chargé
if (!defined('ABSPATH')) {
    exit;
}

function traiter_formulaire() {
    global $wpdb;

    // Vérification du nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'submit_form_nonce')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité.'));
    }

    // Validation des données
    $erreurs = [];
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $statut = isset($_POST['statut']) ? sanitize_text_field($_POST['statut']) : '';
    $produit = isset($_POST['produit']) ? sanitize_text_field($_POST['produit']) : '';
    $nom = isset($_POST['nom']) ? sanitize_text_field($_POST['nom']) : '';
    $livraison = isset($_POST['livraison']) ? sanitize_text_field($_POST['livraison']) : '';
    $telephone = isset($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : '';

    if (empty($email)) {
        $erreurs['email'] = "L'email est obligatoire.";
    } elseif (!is_email($email)) {
        $erreurs['email'] = "L'email n'est pas valide.";
    }

    if (empty($statut)) $erreurs['statut'] = "Le statut est obligatoire.";
    if (empty($produit)) $erreurs['produit'] = "Le produit est obligatoire.";

    if (!empty($erreurs)) {
        wp_send_json(array(
            'erreurs' => $erreurs,
            'message_global' => 'Veuillez corriger les erreurs avant d\'envoyer ce formulaire.'
        ));
    } else {
        // Traitement et insertion en base de données
        // wp_send_json_success(array(
        //     'message_global' => 'Formulaire soumis avec succès!',
        //     'redirect_url' => 'https://example.com/success'
        // ));
         // Préparation des données pour insertion
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

        // Insertion dans la base de données
        $format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s'];
        $insertion = $wpdb->insert($table_name, $donnees, $format);

       if ($insertion === false) {
         error_log("Erreur SQL : " . $wpdb->last_error);
         wp_send_json_error(array('message' => 'Erreur lors de l\'insertion dans la base de données.'));
       }

       $to = 'info@goliat.fr';
      $subject = 'Nouveau prospect';
      $message = sprintf(
        "Statut: %s\nNom: %s\nTéléphone: %s\nEmail: %s\nProduit: %s\nLieu de livraison: %s",
        $statut, $nom, $telephone, $email, $produit, $livraison
      );
      if (!wp_mail($to, $subject, $message)) {
        wp_send_json_error(array('message' => 'Erreur lors de l\'envoi de l\'email.'));
      }

      // Retour succès
     wp_send_json_success(array(
        'message_global' => 'Formulaire soumis avec succès.',
        'redirect_url' => home_url('/validation-form/')
    ));
    }
}
add_action('wp_ajax_submit', 'traiter_formulaire');
add_action('wp_ajax_nopriv_submit', 'traiter_formulaire');

   


