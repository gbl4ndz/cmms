import api from './api'

export default {
  list:   (params)   => api.get('/contractors', { params }),
  get:    (id)       => api.get(`/contractors/${id}`),
  create: (data)     => api.post('/contractors', data),
  update: (id, data) => api.put(`/contractors/${id}`, data),
  remove: (id)       => api.delete(`/contractors/${id}`),
}
