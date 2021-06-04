import request from '@/utils/request';

export function fetchServers(query) {
  return request({
    url: '/servers',
    method: 'get',
    params: query,
  });
}

export function fetchArticle(id) {
  return request({
    url: '/articles/' + id,
    method: 'get',
  });
}
export function fetchEditServer(id) {
  return request({
    url: '/servers/' + id,
    method: 'get',
  });
}
export function fetchPv(id) {
  return request({
    url: '/articles/' + id + '/pageviews',
    method: 'get',
  });
}

export function createServer(data) {
  return request({
    url: '/servers/create',
    method: 'post',
    data,
  });
}

export function updateArticle(data) {
  return request({
    url: '/article/update',
    method: 'post',
    data,
  });
}
export function UpdateServer(data) {
  return request({
    url: '/servers/update',
    method: 'post',
    data,
  });
}
export function deleteServer(id) {
  return request({
    url: '/servers/delete/' + id,
    method: 'delete',
  });
}
export function RequestServer(id) {
  return request({
    url: '/servers/request/' + id,
    method: 'get',
  });
}
export function createServerType(data) {
  return request({
    url: '/server_type/create',
    method: 'post',
    data,
  });
}
export function getServerTypes() {
  return request({
    url: '/server_type/getTypes',
    method: 'get',
  });
}
export function fetchErrors(type) {
  return request({
    url: '/servers/errors/' + type,
    method: 'get',
    type,
  });
}
