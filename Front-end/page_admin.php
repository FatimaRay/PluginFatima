<?php
ob_start();
// Déclarations 'use' pour PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
?>
<div class="wrap">
    <h1>Prospects</h1><br><br>

    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="page" value="fg_entrees">
    <input type="hidden" name="action" value="filter_prospects"> 

    <!-- Champs de date -->
    <label for="date_debut">Start Date:</label>
    <input type="date" id="date_debut" name="date_debut"
           value="<?php 
                      echo isset($_GET['date_debut']) ? esc_attr($_GET['date_debut']) : ''; 
                    ?>">

    <label for="date_fin">End Date :</label>
    <input type="date" id="date_fin" name="date_fin"
           value="<?php 
                    echo isset($_GET['date_fin']) ? esc_attr($_GET['date_fin']) : ''; 
                    ?>"> 

    <!-- Boutons avec une action spécifique -->
    <button type="submit" class="supprimer" name="action" value="delete_prospects">
        Déplacer vers la corbeille
    </button>
    <button type="submit" class="filtrer" name="action" value="filter_prospects">Filtrer les entrées</button> 
    <button type="submit" class="exporter" name="action" value="export_excel">Exporter vers Excel</button><br><br><br>

    <!-- Table des prospects -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all"></th>
                <th>#</th>
                <th>Statut</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Produit</th>
                <th>Lieu de livraison</th>
                <th>gclid_field</th>
                <th>Date de création</th>
            </tr>
        </thead>
        <tbody>
        <?php 
            global $wpdb;
            $table_name = $wpdb->prefix . 'fg_entrees';
            $date_debut = isset($_GET['date_debut']) ? sanitize_text_field($_GET['date_debut']) : '';
            $date_fin = isset($_GET['date_fin']) ? sanitize_text_field($_GET['date_fin']) : '';

            $query = "SELECT * FROM $table_name WHERE 1=1";

            if ($date_debut) {
                $query .= $wpdb->prepare(" AND created_at >= %s", $date_debut . ' 00:00:00');
            }
            if ($date_fin) {
                $query .= $wpdb->prepare(" AND created_at <= %s", $date_fin . ' 23:59:59');
            }

            $query .= " ORDER BY created_at DESC LIMIT 200";
            $results = $wpdb->get_results($query);

            foreach ($results as $entrees) {
                echo "<tr class='ligne-prospect'>
                        <td><input type='checkbox' name='prospects_ids[]' value='" . esc_attr($entrees->id) . "'></td>
                        <td>{$entrees->id}
                            <div class='options' style='display: none;'>
                              <a href='" . admin_url("admin-post.php?action=delete_prospect_single&id={$entrees->id}") . "' class='action-supprimer'>Supprimer<span> /</span></a> 
                              <a href='" . admin_url("admin-post.php?action=export_prospect_single&id={$entrees->id}") . "' class='action-exporter'>Exporter</a>
                            </div>
                        </td>
                        <td>{$entrees->statut}</td>
                        <td>{$entrees->nom}</td>
                        <td>{$entrees->telephone}</td>
                        <td>{$entrees->email}</td>
                        <td>{$entrees->produit}</td>
                        <td>{$entrees->livraison}</td>
                        <td>{$entrees->gclid}</td>
                        <td>{$entrees->created_at}</td>
                     </tr>";
            }
        ?>
        </tbody>
    </table>
</form>

<!-- message d'érreur si date fin<date-debut ou pas de données dans une plage de date -->
<?php if (isset($error_message)) : ?>
    <div class="notice notice-error">
        <p><?php echo esc_html($error_message); ?></p>
    </div>
<?php endif; ?>

<!-- messahe d'érreur si aucune donnée à exporter -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'no_data') : ?>
    <div class="notice notice-error is-dismissible">
        <p>Aucun prospect à exporter.</p>
    </div>
<?php endif; ?>


</div>

<style>
    .supprimer{
        margin-left: 28px;
        font-size: 14px;
        background-color:#CE0000;
        color: white;
        height: 35px;
        border-radius: 8px;
        border: none;

    }
    .filtrer{
        margin-left: 28px;
        font-size: 14px;
        background-color:#135e96;
        color: white;
        height: 35px;
        border-radius: 8px;
        border: none;
        width: 160px;
    }
    .exporter{
        margin-left: 28px;
        font-size: 14px;
        background-color:green;
        color: white;
        height: 35px;
        border-radius: 8px;
        border: none;
        width: 160px;
    }
    /* Styles pour le survol */
    .ligne-prospect:hover .options {
    display: block !important;
    position: absolute !important;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    padding: 5px;
    z-index: 10;
    font-size: 13px !important;
   }
   .ligne-prospect .options .action-supprimer{
     color:red !important;
     border: none;
   }
   .ligne-prospect .options .action-exporter{
     color: green !important;
     border: none;
   }
   .ligne-prospect .options .action-supprimer span{
    color:black;
   }


</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const deleteButton = document.querySelector('button[name="action"][value="delete_prospects"]');

    if (deleteButton) {
        deleteButton.addEventListener('click', function(event) {
            // Sélectionner les cases cochées
            const selectedCheckboxes = document.querySelectorAll('input[name="prospects_ids[]"]:checked');

            // Si aucune case n'est cochée
            if (selectedCheckboxes.length === 0) {
                event.preventDefault(); // Empêcher la soumission du formulaire
                alert('Veuillez sélectionner au moins un prospect à supprimer.');
                return; // Arrêter l'exécution du reste du code
            }
            else{
                 // Si au moins une case est cochée, demander confirmation
            const confirmation = confirm('Êtes-vous sûr de vouloir supprimer les prospects sélectionnés ?');
            if (!confirmation) {
                event.preventDefault(); // Annuler la suppression si l'utilisateur clique sur "Annuler"
            }
            }
        });
    }
});
</script>

