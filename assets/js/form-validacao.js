$(document).ready(function() {
    // Máscara para o telefone, aceitando tanto celular quanto fixo
    var phoneMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    };

    var options = {
        onKeyPress: function(val, e, field, options) {
            field.mask(phoneMaskBehavior.apply({}, arguments), options);
        }
    };

    $('#telefone').mask(phoneMaskBehavior, options);

    // Validação do formulário
    $('#form-requisicao').on('submit', function(event) {
        event.preventDefault(); // Impede o envio do formulário
        
        // Validação do nome (mínimo 5 caracteres)
        var nome = $('#nome').val().trim();
        if (nome.length < 5) {
            Swal.fire({
                title: 'Erro!',
                text: 'O nome deve ter no mínimo 5 caracteres.',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return false;
        }

        // Validação de e-mail
        var email = $('#email').val().trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            Swal.fire({
                title: 'Erro!',
                text: 'Por favor, insira um e-mail válido.',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return false;
        }

        // Validação do telefone já é feita pela máscara
        var telefone = $('#telefone').val().trim();
        if (telefone.length < 14) {
            Swal.fire({
                title: 'Erro!',
                text: 'Por favor, insira um número de telefone válido.',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return false;
        }

        // Envio via AJAX se tudo estiver correto
        $.ajax({
            url: 'processa_formulario.php', // Backend que processa o formulário
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Formulário enviado com sucesso.',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao enviar o formulário. Tente novamente.',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            }
        });
    });
});
