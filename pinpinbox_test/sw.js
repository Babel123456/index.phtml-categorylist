'use strict';

self.addEventListener('install', function (event) {
	event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function (event) {
	event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
	event.waitUntil(
		self.registration.pushManager.getSubscription().then(function (subscription) {
		    if (subscription) {
		        fetch('/index/serviceworker/notification', {
		        	method: 'POST',
		        	headers: {
		        		'Content-Type': 'application/x-www-form-urlencoded'
		        	},
		        	body: 'token=' + subscription.endpoint
		    	}).then(function (response) {
					if (response.ok) {
						return response.json().then(function (data) {
							if (data) {
								return self.registration.showNotification(data.title, {
									body: data.body,
									data: data.data,
									icon: data.icon,
								});
							}
						});
					}
		        }).catch(function (error) {
					console.log('There has been a problem with your fetch operation: ' + error.message);
				});
		    }
		})
	);
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
		clients.matchAll({
			type: 'window'
		})
		.then(function (windowClients) {
			for (var i = 0; i < windowClients.length; i++) {
				var client = windowClients[i];
				if (client.url === event.notification.data.url && 'focus' in client) {
					return client.focus();
				}
			}
			if (clients.openWindow) {
				return clients.openWindow(event.notification.data.url);
			}
		})
    );
});