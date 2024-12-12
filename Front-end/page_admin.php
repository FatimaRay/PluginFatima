<?php
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
         
        <button class="bouton" name="trash" value=1 onclick="return confirm('Êtes-vous sûr de vouloir supprimer les prospects sélectionnés ?')">Déplacer vers la corbeille</button>
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
                                        <td><input type='checkbox' name='prospects_ids[]'' value='<?php echo $entrees->id; ?>'</td>
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
         
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Vérifie si le bouton de suppression est bien cliqué
                if (isset($_POST['trash']) && !empty($_POST['prospects_ids'])) {
                    $prospects_ids = array_map('intval', $_POST['prospects_ids']);
                    error_log('ID des prospects à supprimer : ' . implode(', ', $prospects_ids));
            
                    $placeholders = implode(',', array_fill(0, count($prospects_ids), '%d'));
                    
                    // Exécution de la requête de suppression
                    $result = $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET deleted = 1 WHERE id IN ($placeholders)", ...$prospects_ids)
                    );
                    
                    if ($result === false) {
                        error_log('Erreur de suppression : ' . $wpdb->last_error);
                    } else {
                        error_log('Suppression réussie');
                    }
            
                    wp_redirect($_SERVER['REQUEST_URI']);
                    exit();
                } else {
                    error_log('Pas de prospects sélectionnés pour suppression');
                }
            }
            

            if (isset($_GET['export'])) {

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
                    $sheet->setCellValue('A1' . $row, $entrees->id)
                   ->setCellValue('B1' . $row, $entrees->statut)
                   ->setCellValue('C1' . $row, $entrees->nom)
                   ->setCellValue('D1' . $row, $entrees->telephone)
                   ->setCellValue('E1' . $row, $entrees->email)
                   ->setCellValue('F1' . $row, $entrees->produit)
                   ->setCellValue('G1' . $row, $entrees->livraison)
                   ->setCellValue('H1' . $row, $entrees->created_at);
                   $row++;
                }
             // Télécharger le fichier Excel
             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
             header('Content-Disposition: attachment; filename="prospects.xlsx"');
             $writer = new Xlsx($spreadsheet);
             $writer->save('php://output');
              exit();

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
    // Script pour sélectionner/désélectionner tous les prospects
    // document.getElementById('select_all').addEventListener('change', function() {
    //     const checkboxes = document.querySelectorAll('input[name="prospects_ids[]"]');
    //     checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    // });
    document.getElementById('select_all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="prospects_ids[]"]');
    console.log(checkboxes.length);  // Vérifie combien de cases à cocher sont présentes
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    if (selectedCheckboxes.length === 0) {
        e.
        preventDefault();
        alert('Veuillez sélectionner au moins un prospect à supprimer.');
    }
    });
</script>

