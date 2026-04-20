import api from './api'

export default {
  list:   (params)   => api.get('/areas', { params }),
  get:    (id)       => api.get(`/areas/${id}`),
  create: (data)     => api.post('/areas', data),
  update: (id, data) => api.put(`/areas/${id}`, data),
  remove: (id)       => api.delete(`/areas/${id}`),
}
