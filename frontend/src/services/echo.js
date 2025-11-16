import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echoInstance = null

export function initEcho(token) {
  if (echoInstance) {
    return echoInstance
  }

  echoInstance = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`
      }
    },
    authEndpoint: `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/broadcasting/auth`
  })

  console.log('Echo initialized with Pusher:', {
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    authEndpoint: `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/broadcasting/auth`
  })

  return echoInstance
}

export function disconnectEcho(echo) {
  if (echo) {
    echo.disconnect()
  }
  echoInstance = null
}

export function getEcho() {
  return echoInstance
}
