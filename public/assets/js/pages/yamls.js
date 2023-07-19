let yaml_editor = CodeJar(document.querySelector('.yaml-editor'), hljs.highlightElement);
let prev_editor = CodeJar(document.querySelector('.editor-preview'), hljs.highlightElement);

$('[page="yaml"]').on('click','[b-add-yaml]',function() {
    $('#yaml-modal').modal('show');
    $('[f-yaml-id]').val(0);
    $('[f-yaml-name]').val("");
    yaml_editor.updateCode('');
})

$('[page="yaml"]').on('click','[b-edit-yaml]', async function() {

    let env = $(this).closest('tr').attr('yaml');
    
    const response = await axiosRequest({ 
        url: `/yaml/${env}`,
    });

    if (!response.success){
        alertify.warning("Something went wrong while loading yaml file.");
        console.log(response);
        return;
    }

    if(!response.data.name) {
        alertify.warning("Yaml not found.");
        return;
    }

    $('#yaml-modal').modal('show');
    $('[f-yaml-id]').val(response.data.id);
    $('[f-yaml-name]').val(response.data.name);
    yaml_editor.updateCode(response.data.content);
})

$('[page="yaml"]').on('click','[b-delete-yaml]', function() {
    let env = $(this).closest('tr').attr('yaml');

    alertify.confirm("Confirm", "Are you sure you want to delete this yaml file?",
        function(){
            deleteEnv(env);
        },
        function(){
            alertify.warning('Canceled');
        }
    );
})

async function deleteEnv(env) {
    const response = await axiosRequest({ 
        method : "delete",
        url: `/yaml/${env}`,
    });

    if (!response.success){
        alertify.warning("Something went wrong while deleting environment.");
        console.log(response);
        return;
    }

    alertify.success("Environment deleted.");
    setTimeout(() => {
        window.location.reload();
    }, 200);
}

$('#yaml-modal').on('click','[b-save-yaml]',async function() {
    if($('[f-yaml-name]').val() == '') {
        alertify.warning('Please fill up yaml file name.');
        return false;
    }

    if(yaml_editor.toString() == '') {
        alertify.warning('Please fill up at least some commands on yaml file .');
        return false;
    }
    
    const response = await axiosRequest({ 
        method : "post",
        url: `/yaml`,
        data : {
            id: $('[f-yaml-id]').val(),
            name: $('[f-yaml-name]').val(),
            content: yaml_editor.toString()
        }
    });

    // if (response.status == 400) {
    //     alertify.warning("<b>Invalid .yaml</b><br>"+response.data.message);
    //     return;
    // }

    if (!response.success){
        alertify.warning("Something went wrong while saving yaml file.");
        console.log(response);
        return;
    }

    alertify.success("Yaml saved.");
    setTimeout(() => {
        window.location.reload();
    }, 200);
})

$('[page="yaml"]').on('click','[b-versions]', async function() {
    let env = $(this).closest('tr').attr('yaml');

    const response = await axiosRequest({ 
        url: `/yaml/versions/${env}`,
    });
    if (!response.success){
        alertify.warning("Something went wrong while loading yaml versions.");
        console.log(response);
        return;
    }

    $('#versions-modal').modal('show');
    $('.editor-preview').parent().hide();
    $('[versions-table]').parent().show();

    $('[versions-table] tbody').html('');
    response.data.forEach(version => {
        $('[versions-table] tbody').append(`
            <tr>
                <td>${version.id}</td>
                <td>${version.version_date}</td>
                <td>
                    <button class="btn btn-sm btn-primary" title="Restore this version" b-restore-version="${version.id}">
                        <i class="fa fa-history"></i>
                    </button>
                    <button class="btn btn-sm btn-dark" title="See version content" b-see-version="${version.id}">
                        <i class="fa fa-eye "></i>
                    </button>
                </td>
            </tr>
        `);
    } );
})

$('#versions-modal').on('click','[b-restore-version]',function() {
    const version_id = $(this).attr('b-restore-version');

    alertify.confirm("Confirm", "Are you sure you want to restore this version?",
        function(){
            restoreVersion(version_id);
        },
        function(){
            alertify.warning('Canceled');
        }
    );
})

async function restoreVersion(version_id) { 
    const response = await axiosRequest({ 
        method : "put",
        url: `/yaml/restore/${version_id}`,
    });

    if (!response.success){
        alertify.warning("Something went wrong while restoring version.");
        console.log(response);
        return;
    }

    alertify.success("Version restored.");
    setTimeout(() => {
        window.location.reload();
    }, 200);
}

$('#versions-modal').on('click','[b-close-preview]',async function() {
    $('.editor-preview').parent().hide();
    $('[versions-table]').parent().show();
})

$('#versions-modal').on('click','[b-see-version]',async function() {
    const version_id = $(this).attr('b-see-version');
    $('[b-restore-version]').attr('b-restore-version', version_id);
    const response = await axiosRequest({ 
        url: `/yaml/version/${version_id}`,
    });
    if (!response.success){
        alertify.warning("Something went wrong while loading yaml version.");
        console.log(response);
        return;
    }

    $('.editor-preview').parent().show();
    $('[versions-table]').parent().hide();
    prev_editor.updateCode(response.data);
})

