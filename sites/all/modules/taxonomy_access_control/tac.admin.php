<?php


function tac_admin($form, $form_state, $rid = NULL)
{    
    $vocabularyObjects = taxonomy_get_vocabularies();
    $vocabularies = array(-1 => '[Select One]');
    foreach ($vocabularyObjects as $vocabularyObject) {
        $vocabularies[$vocabularyObject->vid] = $vocabularyObject->name;
    }
    
    $vocabulary = variable_get('tac_vocabulary', -1);
    
    $form = array();
    $form[] = array(
        'vocabulary' => array(
              '#type' => 'select',
              '#options' => $vocabularies,
              '#title' => t('Vocabulary to use for Access Control'),
              '#default_value' => $vocabulary
        )
    );
    
    if ($vocabulary > 0) {
        
        $query = db_select('tac_map', 'm');
        $query->fields('m');
        $data = $query->execute()->fetchAll();
        
        $currentValues = array();
        foreach ($data as $row) {
            $currentValues[$row->rid][$row->tid] = $row;
        }
                
        foreach (user_roles() as $rid => $role) {
            if ($rid == DRUPAL_ANONYMOUS_RID) {
                continue;
            }
            $subform = array(
            	'#theme' => 'tac_term_list',
            	'#title' => 'Permissions for role "' . $role . '"'
            );
            foreach (taxonomy_get_tree($vocabulary) as $term) {
                $subform['term_' . $term->tid] = array(
                    '#title'   => $term->name,
                    'view' => array(
                        '#parents' => array('edit', $rid, $term->tid, 'view'),
                        '#type' => 'checkbox',
                        '#default_value' => (isset($currentValues[$rid][$term->tid]->grant_view) ? $currentValues[$rid][$term->tid]->grant_view : 0)
                    ),
                    'update' => array(
                        '#parents' => array('edit', $rid, $term->tid, 'update'),
                        '#type' => 'checkbox',
                        '#default_value' => (isset($currentValues[$rid][$term->tid]->grant_update) ? $currentValues[$rid][$term->tid]->grant_update : 0)
                    ),
                    'delete' => array(
                        '#parents' => array('edit', $rid, $term->tid, 'delete'),
                        '#type' => 'checkbox',
                        '#default_value' => (isset($currentValues[$rid][$term->tid]->grant_delete) ? $currentValues[$rid][$term->tid]->grant_delete : 0)
                    )
                );
            }
            $form['role' . $rid] = $subform;
        }
    }
    
    $form[] = array(
  		'#type' => 'submit',
  		'#value' => t('Submit'),
    );
    
    return $form;
}

function theme_tac_term_list($variables)
{
    $form = $variables['form'];
    
    $headers = array('Term', 'View', 'Update', 'Delete');
    $rows = array();
    foreach (element_children($form) as $key) {
        $rows[] = array(
        	'data' => array(
                $form[$key]['#title'],
                drupal_render($form[$key]['view']),
                drupal_render($form[$key]['update']),
                drupal_render($form[$key]['delete']),
            )
        );
    }
    
    return theme('table', array('header' => $headers, 'rows' => $rows, 'caption' => $form['#title']));
}

function tac_admin_submit($form, &$form_state)
{
    db_delete('tac_map')->execute();
        
    $vocabulary = $form_state['values']['vocabulary'];
    if ($vocabulary > 0 && $vocabulary != variable_get('tac_vocabulary')) {
        variable_set('tac_vocabulary', $vocabulary);
        node_access_needs_rebuild(TRUE);
        return;
    } else if ($vocabulary <= 0) {
        variable_del('tac_vocabulary');
        node_access_needs_rebuild(TRUE);
        return;
    }
    
    
    $insert = db_insert('tac_map')->fields(array('rid', 'tid', 'grant_view', 'grant_update', 'grant_delete'));
    
    foreach ($form_state['values']['edit'] as $rid => $terms) {
        foreach ($terms as $tid => $grants) {
            $insert->values(array($rid, $tid, $grants['view'], $grants['update'], $grants['delete']));
        }
    }
    
    $insert->execute();
}

