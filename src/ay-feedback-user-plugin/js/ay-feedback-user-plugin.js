jQuery(document).ready(function($) {
	$('.feedback-container').each(function() {
		var feedbackContainer = $(this),
			feedbackForm = feedbackContainer.find('.feedback-form'),
			feedbackSubmitButton = feedbackForm.find('.feedback__submit-button');
		feedbackSubmitButton.on('click', function(event) {
			event.preventDefault();
			feedbackSubmitButton.attr("disabled", true);
			var text = feedbackForm.find('.feedback__textarea').val(),
				title = feedbackForm.find('.feedback__name').text(),
				userID = feedbackForm.find('.feedback__name').data('user-id'),
				link = feedbackForm.find('.feedback__link').val();
			$.ajax({
				type: 'POST',
				url: php_data.ajaxUrl,
				data: {
					'action': 'feedback_insert_post',
					'title': title,
					'text': text,
					'link': link,
					'user_id': userID
				},
				success: function(data) {
					feedbackSubmitButton.attr("disabled", false);
					feedbackContainer.find(".feedback-form__notice").addClass('feedback-form__notice_' + data.success).text(data.data);
					if (data.success) {
						feedbackForm.remove();
					}
				},
			});
		})
	})
	$('.feedback-load-more-button').on('click', function(event) {
		event.preventDefault();
		var loadMoreButton = $(this),
			posts = loadMoreButton.attr('data-posts'),
			currentPage = loadMoreButton.data('current-page'),
			maxPage = loadMoreButton.data('max-page'),
			feedbackWarpper = $('.feedback');
		loadMoreButton.attr("disabled", true);
		$.ajax({
			url: php_data.ajaxUrl,
			data: {
				'action': 'feedback_load_more',
				'query': posts,
				'page': currentPage,
			},
			type: 'POST',
			success: function(data) {
				loadMoreButton.attr("disabled", false);
				if (data) {
					$(feedbackWarpper).append(data);
					currentPage++;
					loadMoreButton.data('current-page', currentPage);
					if (currentPage == maxPage) {
						loadMoreButton.remove()
					};
				} else {
					loadMoreButton.remove();
				}
			}
		});
	});
	$('.feedback-add-button').on('click', function(event) {
		event.preventDefault();
		$(this).toggleClass('feedback-add-button_active');
		$('.feedback-container').fadeToggle();
	});
});