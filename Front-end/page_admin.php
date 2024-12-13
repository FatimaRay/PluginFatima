<?php
ob_start();
// Charger PhpSpreadsheet
require_once __DIR__ . '/../vendor/autoload.php';
// Déclarations 'use' pour PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
?>
<div class="wrap">
    <h1>Prospects</h1><br><br>

    <form method="POST" action="">
        <input type="hidden" name="page" value="fg_entrees"> <!--  associer le formulaire à la page actuelle  -->

        <label for="date_debut">Start Date:</label>
        <input type="date" id="date_debut" name="date_debut"
               value="<?php echo isset($_GET['date_debut']) ? esc_attr($_GET['date_debut']) : ''; ?>"> 

        <label for="date_fin">End Date :</label>
        <input type="date" id="date_fin" name="date_fin"
               value="<?php echo isset($_GET['date_fin']) ? esc_attr($_GET['date_fin']) : ''; ?>"> <!-- Remplir le champs avec la valeur date fin si elle est définie -->
         
        <button type="submit" class="bouton" name="trash" value=1 onclick="return confirm('Êtes-vous sûr de vouloir supprimer les prospects sélectionnés ?')">Déplacer vers la corbeille</button>
        <button type="submit" class="bouton" id="filtre" name="filtre">Filtrer les entrées</button>
        <button type="submit" class="bouton" name="export" value="1">Exporter vers Excel</button> <br> <br>

    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all"></th>
                <th>ID</th>
                <th>Statut</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Produit</th>
                <th>Lieu de livraison</th>
                <th>Date de création</th>
            </tr>
        </thead>
        <tbody>
        <?php 
            
              global $wpdb;
              $table_name = $wpdb->prefix . 'fg_entrees';


              if (isset($_POST['trash'])) {
                // Vérifiez si des prospects ont été sélectionnés
                if (isset($_POST['prospects_ids']) && !empty($_POST['prospects_ids'])) {
                    foreach ($_POST['prospects_ids'] as $prospect_id) {
                        // Suppression de chaque prospect de la base de données
                        $wpdb->delete($table_name, ['id' => intval($prospect_id)]);
                    }
                    echo '<div class="notice notice-success"><p>' . esc_html__('Les prospects sélectionnés ont été déplacés vers la corbeille.', 'fg') . '</p></div>';
                } else if(isset($_POST['prospects_ids']) && empty($_POST['prospects_ids'])) {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Veuillez sélectionner au moins un prospect à supprimer.', 'fg') . '</p></div>';
                }
            }

              $date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
              $date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
              $query = "SELECT * FROM $table_name WHERE 1=1";

              if ($date_debut) {
                $query .= $wpdb->prepare(" AND created_at >= %s", $date_debut . ' 00:00:00');
              }
             if ($date_fin) {
                $query .= $wpdb->prepare(" AND created_at <= %s", $date_fin . ' 23:59:59');
             }
             if (isset($_GET['filtre']) && $date_fin < $date_debut) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Veuillez entrer une date de fin supérieure ou égale à la date de début.', 'fg') . '</p></div>';
             }
            
             $query .= " ORDER BY created_at DESC LIMIT 100"; 
             $results = $wpdb->get_results($query);

             foreach ($results as $entrees) {
                            echo "<tr>
                                        <td><input type='checkbox' name='prospects_ids[]' value='<?php echo esc_attr($entrees->id); ?>'</td>
                                        <td>{$entrees->id}</td>
                                        <td>{$entrees->statut}</td>
                                        <td>{$entrees->nom}</td>
                                        <td>{$entrees->telephone}</td>
                                        <td>{$entrees->email}</td>
                                        <td>{$entrees->produit}</td>
                                        <td>{$entrees->livraison}</td>
                                        <td>{$entrees->created_at}</td>
                                    </tr>";
               }
            
            if (isset($_POST['export'])) {
                // Créer un nouveau fichier Excel
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Ajouter l'en-tête
                $sheet->setCellValue('A1', 'ID')
                      ->setCellValue('B1', 'Statut')
                      ->setCellValue('C1', 'Nom')
                      ->setCellValue('D1', 'Téléphone')
                      ->setCellValue('E1', 'Email')
                      ->setCellValue('F1', 'Produit')
                      ->setCellValue('G1', 'Lieu de livraison')
                      ->setCellValue('H1', 'Date de création');

                // Ajouter les données
                $row = 2; // Commence à la ligne 2
                foreach ($results as $entrees) {
                    $sheet->setCellValue('A' . $row, $entrees->id)
                          ->setCellValue('B' . $row, $entrees->statut)
                          ->setCellValue('C' . $row, $entrees->nom)
                          ->setCellValue('D' . $row, $entrees->telephone)
                          ->setCellValue('E' . $row, $entrees->email)
                          ->setCellValue('F' . $row, $entrees->produit)
                          ->setCellValue('G' . $row, $entrees->livraison)
                          ->setCellValue('H' . $row, $entrees->created_at);
                    $row++;
                }

                // Télécharger le fichier Excel
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="prospects.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                exit();
            

                // Ajout du code pour gérer la suppression par AJAX
                add_action('wp_ajax_delete_prospects', 'delete_prospects_ajax_handler');
           }    
        ?>          
        </tbody>
    </table>
</div>
<style>
    .bouton{
        margin-left: 25px;
        text: size 14px;
    }
</style>    
<script>
    document.getElementById('select_all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="prospects_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
   });

  document.querySelector('button[name="trash"]').addEventListener('click', function(event) {
    const selectedCheckboxes = document.querySelectorAll('input[name="prospects_ids[]"]:checked');
    if (selectedCheckboxes.length === 0) {
        event.preventDefault();  // Empêcher la soumission du formulaire
        alert('Veuillez sélectionner au moins un prospect à supprimer.');
    }
   });
</script>
<?php
// Fin du buffering de sortie
ob_end_flush();
?>
