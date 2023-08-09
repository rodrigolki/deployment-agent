let env_editor = CodeJar(document.querySelector('.env-editor'), hljs.highlightElement);
let prev_editor = CodeJar(document.querySelector('.editor-preview'), hljs.highlightElement);

$('[page="env"]').on('click','[b-add-env]',function() {
    $('#environment-modal').modal('show');
    $('[f-env-id]').val(0);
    $('[f-env-type]').val("PLAIN");
    $('[f-env-name], [f-env-env]').val("");
    env_editor.updateCode('');
})

$('[page="env"]').on('click','[b-edit-env]', async function() {

    let env = $(this).closest('tr').attr('env');
    
    const response = await axiosRequest({ 
        url: `/env/${env}`,
    });

    if (!response.success){
        alertify.warning("Something went wrong while loading environment.");
        console.log(response);
        return;
    }

    if(!response.data.name) {
        alertify.warning("Environment not found.");
        return;
    }

    $('#environment-modal').modal('show');
    $('[f-env-id]').val(response.data.id);
    $('[f-env-name]').val(response.data.name);
    $('[f-env-env]').val(response.data.env);
    $('[f-env-type]').val(response.data.content_type);
    env_editor.updateCode(response.data.content);
})

$('[page="env"]').on('click','[b-delete-env]', function() {
    let env = $(this).closest('tr').attr('env');

    alertify.confirm("Confirm", "Are you sure you want to delete this environment?",
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
        url: `/env/${env}`,
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

$('#environment-modal').on('click','[b-save-env]',async function() {
    if($('[f-env-name]').val() == '') {
        alertify.warning('Please fill up environment name.');
        return false;
    }
    if($('[f-env-env]').val() == '') {
        alertify.warning('Please fill up environment.');
        return false;
    }

    if(env_editor.toString() == '') {
        alertify.warning('Please fill up at least one variable.');
        return false;
    }
    
    const response = await axiosRequest({ 
        method : "post",
        url: `/env`,
        data : {
            id: $('[f-env-id]').val(),
            name: $('[f-env-name]').val(),
            env: $('[f-env-env]').val(),
            content: env_editor.toString(),
            content_type: $('[f-env-type]').val()
        }
    });

    if (response.status == 400) {
        alertify.warning("<b>Invalid .env</b><br>"+response.data.message);
        return;
    }

    if (!response.success){
        alertify.warning("Something went wrong while saving environment.");
        console.log(response);
        return;
    }

    alertify.success("Environment saved.");
    setTimeout(() => {
        window.location.reload();
    }, 200);
})

$('[page="env"]').on('click','[b-versions]', async function() {
    let env = $(this).closest('tr').attr('env');

    const response = await axiosRequest({ 
        url: `/env/versions/${env}`,
    });
    if (!response.success){
        alertify.warning("Something went wrong while loading env file versions.");
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
        url: `/env/restore/${version_id}`,
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
        url: `/env/version/${version_id}`,
    });
    if (!response.success){
        alertify.warning("Something went wrong while loading env version.");
        console.log(response);
        return;
    }

    $('.editor-preview').parent().show();
    $('[versions-table]').parent().hide();
    prev_editor.updateCode(response.data);
})

$('[page="env"]').on('click','[b-link-env]',function() {
    const ident = $(this).attr('b-link-env');
    let link = window.location.origin + '/env-file/' + ident;

    //coppy link to clipboard
    let $temp = $("<input>");
    $("body").append($temp);
    $temp.val(link).select();
    document.execCommand("copy");
    $temp.remove();

    alertify.success("Link copied to clipboard.");
})