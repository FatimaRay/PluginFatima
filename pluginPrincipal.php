<?php
      use PhpOffice\PhpSpreadsheet\Spreadsheet;
     use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
?>
<?php 

/*
Plugin Name: FormulaireGoliat
Description: Un plugin de formulaire personnalisé pour remplacer Ninja Forms avec des exigences spécifiques.
Version: 1.0
Author: Rayé Kitou Fatima
*/

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit; 
}

// Création d'un shortcode nommé Frontend
add_shortcode('Frontend', 'fg_shortcode');
function fg_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'Front-end/Index.php';
    return ob_get_clean();
}

// Enregistrement et chargement de la feuille de style et du script
function fg_style_scripts() {
    wp_enqueue_style('fg-styles', plugins_url('Front-end/style.css', __FILE__));
    wp_enqueue_script('fg-scripts', plugin_dir_url(__FILE__) . 'js/traitement.js', array('jquery'), null, true);
    wp_localize_script('fg-scripts', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'fg_style_scripts');



//Création d'une table personnalisée dans la base de données
function fg_create_database_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'fg_entrees';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        statut tinytext NOT NULL,
        nom tinytext NOT NULL,
        telephone varchar(15) NOT NULL,
        email text NOT NULL,
        produit tinytext NOT NULL,
        livraison tinytext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql); 
}
register_activation_hook(__FILE__, 'fg_create_database_table');

// Insertion dans BD
function fg_soumission_insertion() { 

    global $wpdb;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer les données envoyées via POST
        $statut = isset($_POST['statut']) ? sanitize_text_field($_POST['statut']) : '';
        $nom = isset($_POST['nom']) ? sanitize_text_field($_POST['nom']) : '';
        $telephone = isset($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $produit = isset($_POST['produit']) ? sanitize_text_field($_POST['produit']) : '';
        $livraison = isset($_POST['livraison']) ? sanitize_text_field($_POST['livraison']) : '';

        // Insérer les données dans la table
        $table_name = $wpdb->prefix . 'fg_entrees';
        $insertion=$wpdb->insert(
            $table_name,
            array(
                'statut' => $statut,
                'nom' => $nom,
                'telephone' => $telephone,
                'email' => $email,
                'produit' => $produit,
                'livraison' => $livraison
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

         wp_send_json_success(array('message' => 'Données enregistrées avec succès.'));

        //  Envoi du prospect dans la boite mail de goliat
        $to = 'info@goliat.fr';
      $subject = 'Nouveau prospect';
      $message = sprintf(
        "Statut: %s\nNom: %s\nTéléphone: %s\nEmail: %s\nProduit: %s\nLieu de livraison: %s",
        $statut, $nom, $telephone, $email, $produit, $livraison
      );
      wp_mail($to,$subject,$message);
      if (!wp_mail($to, $subject, $message)) {
        wp_send_json_error(array('message' => 'Erreur lors de l\'envoi de l\'email.'));
      }

        // Envoi au middleware
        $response = wp_remote_post('https://example.com/api', [
            'body' => json_encode($data),
            'headers' => ['Content-Type' => 'application/json']
        ]);

    } else {
        wp_send_json_error(array('message' => 'Requête invalide.'));
    }
}

add_action('wp_ajax_submit', 'fg_soumission_insertion');
add_action('wp_ajax_nopriv_submit', 'fg_soumission_insertion');


// Ajouter la page de menu dans le tableau d'administration de WordPress
add_action('admin_menu', 'fg_admin_menu');
function fg_admin_menu() {
    add_menu_page(
        'Prospects',
        'Formulaire Goliat',
        'manage_options',
        'fg_entrees',
        'fg_entrees_page',
        'dashicons-list-view'
    );
}

// Récupérer les données de la table fg_entrees pour afficher dans l'admin
function fg_entrees_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'fg_entrees';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100");

    include plugin_dir_path(__FILE__) . 'Front-end/page_admin.php';
}

// Exporter vers Excel
function fg_exporter_vers_excel() {
    if (isset($_GET['export']) && $_GET['export'] == '1') {
        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

        global $wpdb;
        $table_name = $wpdb->prefix . 'fg_entrees';

        $date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
        $date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';

        $query = "SELECT * FROM $table_name WHERE 1=1";

        if ($date_debut) {
            $query .= $wpdb->prepare(" AND created_at >= %s", $date_debut . ' 00:00:00');
        }
        if ($date_fin) {
            $query .= $wpdb->prepare(" AND created_at <= %s", $date_fin . ' 23:59:59');
        }
        $query .= " ORDER BY created_at DESC";

        $results = $wpdb->get_results($query);

        // Création du fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajout d'en-têtes au sheet
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Statut');
        $sheet->setCellValue('C1', 'Nom');
        $sheet->setCellValue('D1', 'Téléphone');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Produit');
        $sheet->setCellValue('G1', 'Lieu de livraison');
        $sheet->setCellValue('H1', 'Date de création');

        // Insertion des données dans le sheet
        $row = 2;
        foreach ($results as $entry) {
            $sheet->setCellValue('A1' . $row, $entry->id);
            $sheet->setCellValue('B1' . $row, $entry->statut);
            $sheet->setCellValue('C1' . $row, $entry->nom);
            $sheet->setCellValue('D1' . $row, $entry->telephone);
            $sheet->setCellValue('E1' . $row, $entry->email);
            $sheet->setCellValue('F1' . $row, $entry->produit);
            $sheet->setCellValue('G1' . $row, $entry->livraison);
            $sheet->setCellValue('H1' . $row, $entry->created_at);
            $row++;
        }

        // Génération du fichier Excel
        $filename = 'Prospects_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
                exit;
    }
       
}
add_action('admin_init', 'fg_exporter_vers_excel');
?>