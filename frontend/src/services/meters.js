import api from './api'

export default {
  list:           (params)   => api.get('/meters', { params }),
  get:            (id)       => api.get(`/meters/${id}`),
  create:         (data)     => api.post('/meters', data),
  remove:         (id)       => api.delete(`/meters/${id}`),
  addReading:     (id, data) => api.post(`/meters/${id}/readings`, data),
  resetBaseline:  (id)       => api.post(`/meters/${id}/reset-baseline`),
}
