import api from './api'

export default {
  list:   (params)   => api.get('/parts', { params }),
  get:    (id)       => api.get(`/parts/${id}`),
  create: (data)     => api.post('/parts', data),
  update: (id, data) => api.put(`/parts/${id}`, data),
  remove: (id)       => api.delete(`/parts/${id}`),
}
