<?php
/**
 * This is an example implementation of the redcap_save_record hook at the
 * project level.
 */
function example_save_record($project_id, $record, $instrument, $event_id,
                         $group_id, $survey_hash, $response_id)
{
    echo "EXECUTED: example_save_record(".$project_id.",".$record.",".$instrument
        .",".$event_id.",".$group_id.",".$survey_hash.",".$response_id.")\n";
}
?>
