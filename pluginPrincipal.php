<?php
    // Charger PhpSpreadsheet
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
/*
Plugin Name: FormulaireGoliat
Description: Un plugin pour la collecte des prospects.
Version: 1.0
Author: GOLIAT Cameroun
*/

// Empêcher l'accès direct
if (!defined ('ABSPATH')) {
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

// Enregistrement et chargement de styles et scripts pour l'administration
function fg_admin_style_scripts($hook_suffix) {
    // Vérifiez que vous êtes bien sur la page admin correspondante
    if ($hook_suffix === 'toplevel_page_fg_entrees') { 
        // Enqueue le style CSS pour la page admin
        wp_enqueue_style( 'fg-admin-styles', plugins_url('Frontend/page-admin.css', __FILE__) );
    }
}
add_action('admin_enqueue_scripts', 'fg_admin_style_scripts');




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
        gclid VARCHAR(255) DEFAULT NULL,
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
        $gclid = isset($_POST['gclid']) ? sanitize_text_field($_POST['gclid']) : '';

        // Insérer les données dans la table
        $table_name = $wpdb->prefix . 'fg_entrees';
        $insertion=$wpdb->insert(
            $table_name,
           $data=array(
                'statut' => $statut,
                'nom' => $nom,
                'telephone' => $telephone,
                'email' => $email,
                'produit' => $produit,
                'livraison' => $livraison,
                'gclid' => $gclid,
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );

         wp_send_json_success(array('message' => 'Données enregistrées avec succès.'));

        //  Envoi du prospect dans la boite mail de goliat
        $to = 'info@goliat.fr';
        $subject = 'Nouveau prospect';
        $message = sprintf("Statut: %s\nNom: %s\nTéléplone: %s\nEmail: %s\nProduit: %s\nLieu de livraison: %s\ngclid: %s",
        $data['statut'], $data['nom'], $data['telephone'], $data['email'], $data['produit'], $data['livraison'], $data['gclid']);
        $mail_sent = wp_mail($to, $subject, $message);
        if (!$mail_sent) {
            error_log('Erreur lors de l\'envoi de l\'email : ' . print_r($wpdb->last_error, true));
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

// Récupérer les données de la table fg_entrees pour afficher dans l'admin et pouvoir les filrtrer
function fg_entrees_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fg_entrees';

    // Construction de la requête SQL avec les filtres
    $query = "SELECT * FROM $table_name WHERE 1=1";

     // Récupération des dates à partir des paramètres de l'URL
     $date_debut = isset($_GET['date_debut']) ? sanitize_text_field($_GET['date_debut']) : '';
     $date_fin = isset($_GET['date_fin']) ? sanitize_text_field($_GET['date_fin']) : '';

    if (!empty($date_debut)) {
        $query .= $wpdb->prepare(" AND created_at >= %s", $date_debut . ' 00:00:00');
    }
    if (!empty($date_fin)) {
        $query .= $wpdb->prepare(" AND created_at <= %s", $date_fin . ' 23:59:59');
    }

    $query .= " ORDER BY created_at DESC LIMIT 200";

    // Exécuter la requête
    $results = $wpdb->get_results($query);

    if( (!empty($date_debut)) and !empty($date_fin)){
            if($date_debut<$date_fin){
                $error_message = 'Veuillez entrer une date de fin Supérieure ou Egale à la date de debut.';
            }
            else if (empty($results)) {
                $error_message = 'Aucun prospect trouvé dans la plage de dates sélectionnée.';
           }
    }
    

    // Inclure le fichier de la vue (page_admin.php)
    include plugin_dir_path(__FILE__) . 'Front-end/page_admin.php';
}


add_action('admin_post_filter_prospects', 'fg_filtrer_prospects');
function fg_filtrer_prospects() {
    $date_debut = isset($_POST['date_debut']) ? sanitize_text_field($_POST['date_debut']) : '';
     $date_fin = isset($_POST['date_fin']) ? sanitize_text_field($_POST['date_fin']) : '';
    
    // rediriger vers la page avec les dates de filtre dans l'URL
    $url = add_query_arg(array(
        'date_debut' => $date_debut,
        'date_fin' => $date_fin
    ), admin_url('admin.php?page=fg_entrees'));

    wp_redirect($url);
    exit;
}


// Exportation d'une ou plusieurs lignes vers Excel
add_action('admin_post_export_excel', 'fg_exporter_vers_excel');
function fg_exporter_vers_excel() {
    if  ($_POST['action'] === 'export_excel') {
        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
        global $wpdb;
        $table_name = $wpdb->prefix . 'fg_entrees';
        $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
        $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
        $query = "SELECT * FROM $table_name WHERE 1=1";
        if ($date_debut) {
            $query .= $wpdb->prepare(" AND created_at >= %s", $date_debut . ' 00:00:00');
        }
        if ($date_fin) {
            $query .= $wpdb->prepare(" AND created_at <= %s", $date_fin . ' 23:59:59');
        }
        $query .= " ORDER BY created_at DESC LIMIT 200";
        $results = $wpdb->get_results($query);

        if (empty($results)) {
            // Redirection avec un message d'erreur
            wp_redirect(admin_url('admin.php?page=fg_entrees&error=no_data'));
            exit;
        }
        error_log('Nombre de résultats: ' . count($results));
        // Création du fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // // Ajout d'en-têtes au sheet
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Statut');
        $sheet->setCellValue('C1', 'Nom');
        $sheet->setCellValue('D1', 'Téléphone');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Produit');
        $sheet->setCellValue('G1', 'Lieu de livraison');
        $sheet->setCellValue('H1', 'Date de création');
        $sheet->setCellValue('I1', 'gclig_field');
        // Insertion des données dans le sheet
        $row = 2;
        foreach ($results as $entrees) {
            $sheet->setCellValue('A' . $row, $entrees->id);
            $sheet->setCellValue('B' . $row, $entrees->statut);
            $sheet->setCellValue('C' . $row, $entrees->nom);
            $sheet->setCellValue('D' . $row, $entrees->telephone);
            $sheet->setCellValue('E' . $row, $entrees->email);
            $sheet->setCellValue('F' . $row, $entrees->produit);
            $sheet->setCellValue('G' . $row, $entrees->livraison);
            $sheet->setCellValue('H' . $row, $entrees->created_at);
            $sheet->setCellValue('I' . $row, $entrees->gclid);
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

// Exportation d'une seule ligne vers excel
add_action('admin_post_export_prospect_single', 'export_prospect_single');
function export_prospect_single() {
    if (!empty($_GET['id'])) {
        global $wpdb;
        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

        $table_name = $wpdb->prefix . 'fg_entrees';
        $id = intval($_GET['id']);
        $prospect = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        if (!$prospect) {
            wp_die('Prospect introuvable.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Statut');
        $sheet->setCellValue('C1', 'Nom');
        $sheet->setCellValue('D1', 'Téléphone');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Produit');
        $sheet->setCellValue('G1', 'Lieu de livraison');
        $sheet->setCellValue('H1', 'Date de création');
        $sheet->setCellValue('I1', 'gclid_field');

        $sheet->setCellValue('A2', $prospect->id);
        $sheet->setCellValue('B2', $prospect->statut);
        $sheet->setCellValue('C2', $prospect->nom);
        $sheet->setCellValue('D2', $prospect->telephone);
        $sheet->setCellValue('E2', $prospect->email);
        $sheet->setCellValue('F2', $prospect->produit);
        $sheet->setCellValue('G2', $prospect->livraison);
        $sheet->setCellValue('H2', $prospect->created_at);
        $sheet->setCellValue('I2', $prospect->gclid);

        $filename = 'Prospect_' . $prospect->id . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } else {
        wp_die('ID invalide pour exportation.');
    }
}


//suppression d'une ou plusieurs lignes
function delete_prospects() {
    if (isset($_POST['prospects_ids']) && !empty($_POST['prospects_ids'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fg_entrees';

        // Récupérer les IDs des prospects sélectionnés
        $prospects_ids = array_map('intval', $_POST['prospects_ids']);
        
        // Préparer la requête de suppression
        $placeholders = implode(',', array_fill(0, count($prospects_ids), '%d'));
        $query = "DELETE FROM $table_name WHERE id IN ($placeholders)";
        
        // Exécuter la requête
        $result = $wpdb->query($wpdb->prepare($query, ...$prospects_ids));

        if ($result === false) {
            // Si la requête échoue, afficher un message d'erreur
            wp_die('Erreur lors de la suppression des prospects.');
        } else {
            // Rediriger après la suppression
            wp_redirect(admin_url('admin.php?page=fg_entrees&message=deleted'));
            exit; // Assurez-vous que le script s'arrête après la redirection
        }
    } 
    else {
        wp_die('Aucun prospect sélectionné pour suppression.');
    }
}
add_action('admin_post_delete_prospects', 'delete_prospects');

// suppréssion d'une seule ligne
add_action('admin_post_delete_prospect_single', 'delete_prospect_unique');
function delete_prospect_unique() {
    if (!empty($_GET['id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fg_entrees';
        $id = intval($_GET['id']);

        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));

        if ($result === false) {
            wp_die('Erreur lors de la suppression du prospect.');
        } else {
            wp_redirect(admin_url('admin.php?page=fg_entrees&message=single_deleted'));
            exit;
        }
    } else {
        wp_die('ID invalide pour suppression.');
    }
}



?>