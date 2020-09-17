
$(document).ready(function() {
    
	// Если выбор в чекбоксе - отправка AJAX на ajax.php
	$(document).on('change', '#role', function() {
		
		// Получить id роли из чекбокса
		var role = $("#role").val(); 
		
		// Если это не нулевой пункт "Выберите роль"
		if(role != 0)
		{
			// Отправить AJAX
			$.post('/ajax.php', {role_id:role}, function(data) {
				
				data = JSON.parse(data);
				
				// Заменить результирующий html дерева
				$('#dynamic_tree').html(data);
				
			});
		}
		else
			$('#dynamic_tree').html('');	// Иначе убираем дерево (а вдруг оно там было)
	});
});