import api from './api'

export default {
  list:    (params) => api.get('/locations', { params }),
  get:     (id)     => api.get(`/locations/${id}`),
  create:  (data)   => api.post('/locations', data),
  update:  (id, data) => api.put(`/locations/${id}`, data),
  remove:  (id)     => api.delete(`/locations/${id}`),
}
