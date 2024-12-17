<?php
ob_start();
// Déclarations 'use' pour PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
?>
<div class="wrap">
    <h1>Prospects</h1><br><br>

    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="delete_prospects">
        <input type="hidden" name="page" value="export_excel"> <!--  associer le formulaire à la page actuelle  -->

        <label for="date_debut">Start Date:</label>
        <input type="date" id="date_debut" name="date_debut"
               value="<?php echo isset($_GET['date_debut']) ? esc_attr($_GET['date_debut']) : ''; ?>"> 

        <label for="date_fin">End Date :</label>
        <input type="date" id="date_fin" name="date_fin"
               value="<?php echo isset($_GET['date_fin']) ? esc_attr($_GET['date_fin']) : ''; ?>"> <!-- Remplir le champs avec la valeur date fin si elle est définie -->
         
        <button type="submit" class="bouton" name="trash" value=1 onclick="return confirm('Êtes-vous sûr de vouloir supprimer les prospects sélectionnés ?')">Déplacer vers la corbeille</button>
        <button type="submit" class="bouton" id="filtre" name="filtre">Filtrer les entrées</button>
        <button type="submit" class="bouton" name="export" value="1">Exporter vers Excel</button> <br> <br>

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
                                        <td><input type='checkbox' name='prospects_ids[]' value='<?php echo esc_attr($entrees->id); ?>'></td>
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
        ?>          
        </tbody>
    </table>
    </form>
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
// ob_end_flush();
?>
